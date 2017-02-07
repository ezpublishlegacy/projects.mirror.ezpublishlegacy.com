#!/usr/bin/php
<?php

set_time_limit( 0 );

print( "Starting article import\n" );
include_once( "lib/ezutils/classes/ezdebug.php" );
include_once( "lib/ezutils/classes/ezmodule.php" );
eZModule::setGlobalPathList( array( "kernel" ) );
include_once( 'lib/ezutils/classes/ezexecution.php' );

include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

include_once( 'kernel/classes/ezcontentobject.php' );

include_once( "lib/ezxml/classes/ezxml.php" );

include_once( 'lib/ezlocale/classes/ezlocale.php' );
include_once( 'lib/ezlocale/classes/ezdate.php' );
include_once( 'lib/ezlocale/classes/ezdatetime.php' );

eZDebug::setHandleType( EZ_HANDLE_TO_PHP );
eZDebug::setLogFileEnabled( false );
eZINI::setIsCacheEnabled( false );

function eZDBCleanup()
{
    if ( class_exists( 'ezdb' )
         and eZDB::hasInstance() )
    {
        $db =& eZDB::instance();
        $db->setIsSQLOutputEnabled( false );
    }
}

function eZFatalError()
{
    eZDebug::setHandleType( EZ_HANDLE_NONE );
    print( "Fatal error: eZ publish did not finish it's request\n" );
    print( "The execution of eZ publish was abruptly ended." );
}

eZExecution::addCleanupHandler( 'eZDBCleanup' );
eZExecution::addFatalErrorHandler( 'eZFatalError' );

$ini =& eZINI::instance();

$server = $ini->variable( 'DatabaseSettings', 'Server' );
$user = $ini->variable( 'DatabaseSettings', 'User' );
$pwd = $ini->variable( 'DatabaseSettings', 'Password' );
$dbb = $ini->variable( 'DatabaseSettings', 'Database' );
$datadir = 'import/spool';
$count=0;
eZExecution::cleanup();
eZExecution::setCleanExit();
print( "$server $user $pwd $dbb\n" );
// Could set import range
$articleList =& getAllarticles( 0, 10000 );



foreach ( $articleList as $article )
{
    addArticle( $article );
}
print "End";
function getfiles($dir,$ext=null){
    $ret = array();
    $handle=opendir($dir); 
    while ($file = readdir ($handle)) { 
        if ($file != "." && $file != ".." && preg_match ("/\.".$ext."/i", $file )   ) { 
            $ret[]=$file; 
        } 
    }
    closedir($handle); 
    return $ret;
}
function unzip($file){
    echo "Unzipping $file\n";
    system("gzip -d $file");
}
function spoolfile_to_array($filename){
    $retarr = array();
    global $count;
    $handle = fopen ($filename, "r");
    $contents = fread ($handle, filesize ($filename));
    fclose ($handle);
    
    $emails = preg_split ("/=========================================================================(\r\n|\n)/",$contents,-1,PREG_SPLIT_NO_EMPTY);
    print("File: $filename \n");
    /**
Sample head
Date:         Tue, 4 Jan 2000 11:20:48 -0600
Reply-To:     Steven Clift <clift@PUBLICUS.NET>
Sender:       DO-WIRE - Democracies Online Newswire <DO-WIRE@TC.UMN.EDU>
From:         Steven Clift <clift@PUBLICUS.NET>
Organization: http://www.publicus.net
Subject:      Dutch Newswire on e-democracy, online consultation
MIME-Version: 1.0
Content-type: text/plain; charset=US-ASCII
Content-transfer-encoding: 7BIT
    */
    foreach ($emails as $email){
        list($head,$body) = preg_split("/\n\n\n/",$email,2,PREG_SPLIT_NO_EMPTY);
        $emailarr=array();
        $params = preg_split("/(\r\n|\n)/",$head,-1,PREG_SPLIT_NO_EMPTY);
        foreach ($params as $param){
            list($key,$value)=preg_split("/:/",$param,2,PREG_SPLIT_NO_EMPTY);
            $emailarr["head"][trim($key)] = trim($value);
            if ($key=="Date"){
                $elements = preg_split('/^(\S+), (\S+) (\S+) (\S+) (\S+):(\S+):(\S+) (\S+)/', $emailarr["head"]["Date"],-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                $us_locale =& eZLocale::instance( 'GB' );
                $dt =& eZDateTime::create();
                $dt->setLocale( $us_locale );
                $months["Jan"]=1;
                $months["Feb"]=2;
                $months["Mrz"]=3;
                $months["Apr"]=4;
                $months["May"]=5;
                $months["Jun"]=6;
                $months["Jul"]=7;
                $months["Aug"]=8;
                $months["Sep"]=9;
                $months["Oct"]=10;
                $months["Nov"]=11;
                $months["Dec"]=12;
                $emailarr["head"]["ezdatetime"] = & eZDateTime::create($elements[4],$elements[5],$elements[6],$months[$elements[2]],$elements[1],$elements[3]);
            }
        }
        list($body,$signatur)=preg_split("/\^[\t ]+\^[\t ]+\^[\t ]+\^/",$body,2,PREG_SPLIT_NO_EMPTY);
        $emailarr["body"]=quoted_printable_decode($body);
        if (!empty($emailarr["body"])&&!empty($emailarr["head"]["ezdatetime"]->DateTime)&&!empty($emailarr["head"]["Subject"])){
            $retarr[]=$emailarr; 
        }
        unset($emailarr);
    }
    return $retarr;
} 
function &getAllarticles( $minArticleID, $maxArticleID )
{
    global $datadir;
    $articleArray= array();
    $db =& eZDB::instance();
    $db->setIsSQLOutputEnabled( false );
    $files  = getfiles($datadir,"gz");
   
    
    foreach ($files as $file){
        unzip($datadir.'/'.$file);
    }
    $files  = getfiles($datadir,"log");
     #var_dump($files);
    foreach ($files as $file){
        $articleArray = array_merge($articleArray,spoolfile_to_array($datadir.'/'.$file));
    }
    print ("Assign IDs and transform\n");
    $amount=count($articleArray);
    for($i=0;($i<$amount)&& ($i < $maxArticleID);$i++)
    {
        
        print(($i+1).":-----------CONTENT PREPARING-------------\n");
        $articleArray[$i]["ID"]=$i+1;
        $articleArray[$i]["Name"]=$articleArray[$i]["head"]["Subject"];

include_once( 'lib/ezxml/classes/ezxml.php' );

$doc = new eZDOMDocument();
$doc->setName( 'import' );

$root =& $doc->createElementNode( 'article' );
$doc->setRoot( $root );

$book1 =& $doc->createElementNode( 'generator' );
$book1->appendChild( $doc->createTextNode( "qdom" ) );
$root->appendChild( $book1 );

$book1 =& $doc->createElementNode( 'intro' );
$root->appendChild( $book1 );

$book1 =& $doc->createElementNode( 'body' );
$book2 =& $doc->createElementNode( 'page' );
$book2->appendChild( $doc->createTextNode( $articleArray[$i]["body"] ) );
$book1->appendChild( $book2 );
$root->appendChild( $book1 );

$articleArray[$i]["Contents"] =& $doc->toString();

      # $articleArray[$i]["AuthorID"]=14;
        $articleArray[$i]["Modified"]=$articleArray[$i]["head"]["ezdatetime"]->DateTime;
        $articleArray[$i]["Created"]=$articleArray[$i]["head"]["ezdatetime"]->DateTime;
        $articleArray[$i]["Published"]=$articleArray[$i]["head"]["ezdatetime"]->DateTime;
        $articleArray[$i]["CategoryID"]=1;
        $articleArray[$i]["Keywords"]=$articleArray[$i]["body"];
        
    }
	#$articleArray = $db->arrayQuery( "SELECT eZArticle_Article.ID, eZArticle_Article.Name, eZArticle_Article.Contents, eZArticle_Article.AuthorID,
        #                                     eZArticle_Article.Modified, eZArticle_Article.Created, eZArticle_Article.published, eZArticle_Article.Keywords,
        #                                     eZArticle_ArticleCategoryLink.CategoryID
	#                                 FROM
	#                                         eZArticle_Article, eZArticle_ArticleCategoryLink
	#                                  WHERE
        #                                     eZArticle_Article.ID = eZArticle_ArticleCategoryLink.ArticleID
        #                                AND
	#                                         eZArticle_Article.ID >= $minArticleID AND eZArticle_Article.ID < $maxArticleID
        #                                AND  eZArticle_ArticleCategoryLink.CategoryID != 0
        #                              ORDER BY eZArticle_Article.ID ASC" );
	return $articleArray;
}

function addArticle( &$article )
{
    // If 2.2 article has illegal xml format, add these articles to the list. The import script will import the article
    // with empty content
    $badArticleIDList = array();

    $badArticleIDList[] = 100001;

    $db =& eZDB::instance();
	$db->setIsSQLOutputEnabled( false );
	//fetch folder class
	$class =& eZContentClass::fetch( 2 );
    unset( $contentObject );
    $articleID = $article['ID'];
    $articleName = $article['Name'];
    $articleContent = $article['Contents'];
    $articleAuthorID = $article['AuthorID'];
    $articleModified = $article['Modified'];
    $articleCreated = $article['Created'];
    $articlePublished = $article['Published'];
    $articleKeywords = $article['Keywords'];
    $articleCategoryID = $article['CategoryID'];

    print( "import article " . $articleID . "\n" );
    // set remoteID
    $remoteID = "article_article_" . $articleID ;

    // Check if the article has been imported.
    $existNodeIDArray = $db->arrayQuery( "SELECT id FROM ezcontentobject
                                           WHERE remote_id = '$remoteID'" );

    if ( $existNodeIDArray == null )
    {

        $remoteCategoryID = "article_category_" . $articleCategoryID;
        // Find parent node
        $parentNodeIDArray = $db->arrayQuery( "SELECT ezcontentobject_tree.node_id, ezcontentobject.section_id FROM ezcontentobject, ezcontentobject_tree
                                           WHERE ezcontentobject.remote_id = '$remoteCategoryID' AND ezcontentobject.id = ezcontentobject_tree.contentobject_id" );

        $parentNodeID = $parentNodeIDArray[0]['node_id'];
        $parentSectionID = $parentNodeIDArray[0]['section_id'];

        $remoteUserID = "user_" . $articleAuthorID;
        // Find current user id
        $userIDArray = $db->arrayQuery( "SELECT id FROM ezcontentobject WHERE remote_id = '$remoteUserID'" );

        $userID = $userIDArray[0]['id'];

        // If no exist user, set it to administrator.
        if ( $userID == null )
            $userID = 14;

        if ( $userID != null )
        {
            // Create object by user id in section 1
            $contentObject =& $class->instantiate( $userID, 1 );
            $contentObject->setAttribute('remote_id', $remoteID );
            $contentObject->setAttribute( 'name', $articleName );

            // Find related objects
            $relatedImages = $db->arrayQuery( "SELECT * FROM eZArticle_ArticleImageLink WHERE ArticleID = '$articleID'" );

            if ( $relatedImages != null )
            {
                foreach ( $relatedImages as $relatedImage )
                {
                    $imageID = $relatedImage['ImageID'];
                    $remoteImageID = "image_" . $imageID;
                    $objectIDArray = $db->arrayQuery( "SELECT id FROM ezcontentobject
                                                   WHERE ezcontentobject.remote_id = '$remoteImageID'" );
                    $objectID = $objectIDArray[0]['id'];
                    $contentObject->addContentObjectRelation( $objectID, 1 );
                }
            }

            $relatedFiles = $db->arrayQuery( "SELECT * FROM eZArticle_ArticleFileLink WHERE ArticleID = '$articleID'" );

            if ( $relatedFiles != null )
            {
                foreach ( $relatedFiles as $relatedFile )
                {
                    $fileID = $relatedFile['FileID'];
                    $remoteFileID = "file_" . $fileID;
                    $objectIDArray = $db->arrayQuery( "SELECT id FROM ezcontentobject
                                                   WHERE ezcontentobject.remote_id = '$remoteFileID'" );
                    $objectID = $objectIDArray[0]['id'];
                    $contentObject->addContentObjectRelation( $objectID, 1 );
                }
            }

            $nodeAssignment =& eZNodeAssignment::create( array(
                                                             'contentobject_id' => $contentObject->attribute( 'id' ),
                                                             'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                             'parent_node' => $parentNodeID,
                                                             'sort_field' => 2,
                                                             'sort_order' => 0,
                                                             'is_main' => 1
                                                             )
                                                         );
            $nodeAssignment->store();

            $version =& $contentObject->version( 1 );
            $version->setAttribute( 'modified', $articleModified );
            $version->setAttribute( 'created', $articleCreated );
            $version->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT );
            $version->store();

            $contentObjectID = $contentObject->attribute( 'id' );
            $contentObjectAttributes =& $version->contentObjectAttributes();

            $contentObjectAttributes[0]->setAttribute( 'data_text', $articleName );
            $contentObjectAttributes[0]->store();

            include_once( 'ez22to30xmlimport.php' );
            $contents = $articleContent;
            $xml = new eZXML();
            // replace \n with <br />
            $contents = str_replace( "\r\n\r\n\r\n\r\n", "<p /><p />", $contents );
            $contents = str_replace( "\n\n\n\n", "<p /><p />", $contents );
            $contents = str_replace( "\n\n", "<p />", $contents );
            $contents = str_replace( "\r\n\r\n", "<p />", $contents );
            $contents = str_replace( "\r\n", "<br />", $contents );
            $contents = str_replace( "\n", "<linebreak />", $contents );
            $contents = str_replace( "&lt;br&gt;", "<literalbr />", $contents );
            $contents = str_replace( "&lt;BR&gt;", "<literalbr />", $contents );
            $contents = str_replace( "&lt;p&gt;", "<literalp />", $contents );
            $contents = str_replace( "&lt;P&gt;", "<literalp />", $contents );
            
            if ( !in_array( $articleID,  $badArticleIDList ) )
            {
                
                $inputDOM = $xml->domTree( $contents, array( 'CharsetConversion' => false ) );
                
                $converter = new eZ22To30XMLImport( $articleID );
                $inputText = $converter->convertTo22InputXML( $inputDOM );

                $introData = "<section>";
                $introData .= "<paragraph>";
                $introData .= $inputText[0];
                $introData .= "</paragraph>";
                $introData .= "</section>";

                include_once( "kernel/classes/datatypes/ezxmltext/handlers/input/ezsimplifiedxmlinput.php" );
                $dumpdata = "";
                $simplifiedXMLInput = new eZSimplifiedXMLInput( $dumpdata, null, null );

                $introData = $simplifiedXMLInput->convertInput( $introData );
                $intro = $introData[0]->toString();
                $intro = preg_replace( "#<paragraph> </paragraph>#", "<paragraph>&nbsp;</paragraph>", $intro );
                $intro = str_replace ( "<paragraph />" , "", $intro );
                $intro = str_replace ( "<line />" , "", $intro );
                $intro = str_replace ( "<paragraph></paragraph>" , "", $intro );
                $intro = preg_replace( "#<paragraph>&nbsp;</paragraph>#", "<paragraph />", $intro );
                $intro = preg_replace( "#<paragraph></paragraph>#", "", $intro );

                $intro = preg_replace( "#[\n]+#", "", $intro );
                $intro = preg_replace( "#&lt;/line&gt;#", "\n", $intro );
                $intro = preg_replace( "#&lt;paragraph&gt;#", "\n\n", $intro );
                $contentObjectAttributes[1]->setAttribute( 'data_text', $intro );
                $contentObjectAttributes[1]->store();

                $bodyData = "<section>";
                $bodyData .= "<paragraph>";
                $bodyData .= $inputText[1];
                $bodyData .= "</paragraph>";
                $bodyData .= "</section>";
                $bodyData = $simplifiedXMLInput->convertInput( $bodyData );
                $body = $bodyData[0]->toString();
                $body = preg_replace( "#<paragraph> </paragraph>#", "<paragraph>&nbsp;</paragraph>", $body );
                $body = str_replace ( "<paragraph />" , "", $body );
                $body = str_replace ( "<line />" , "", $body );
                $body = str_replace ( "<paragraph></paragraph>" , "", $body );
                $body = preg_replace( "#<paragraph>&nbsp;</paragraph>#", "<paragraph />", $body );
                $body = preg_replace( "#<paragraph></paragraph>#", "", $body );

                $body = preg_replace( "#[\n]+#", "", $body );
                $body = preg_replace( "#&lt;/line&gt;#", "\n", $body );
                $body = preg_replace( "#&lt;paragraph&gt;#", "\n\n", $body );
                $contentObjectAttributes[2]->setAttribute( 'data_text', $body );
                $contentObjectAttributes[2]->store();
            }

            include_once( 'kernel/classes/datatypes/ezkeyword/ezkeywordtype.php' );
            include_once( 'kernel/classes/datatypes/ezkeyword/ezkeyword.php' );
            $keyword = new eZKeyword();
            $keyword->initializeKeyword( $articleKeywords );
            // $contentObjectAttributes[5]->setContent( $keyword );
            //  $contentObjectAttributes[5]->store();

            include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
            $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $contentObjectID,
                                                                                     'version' => 1 ) );
            $contentObject->setAttribute('modified', $articleModified );
            $contentObject->setAttribute('published', $articlePublished  );
            $contentObject->setAttribute('section_id', $parentSectionID );
            $contentObject->store();
        }
    }
    else
    {
        $isPublished = false;
        $remoteCategoryID = "article_category_" . $articleCategoryID;
        // Find parent node
        $parentNodeIDArray = $db->arrayQuery( "SELECT ezcontentobject_tree.node_id, ezcontentobject.section_id FROM ezcontentobject, ezcontentobject_tree
                                               WHERE ezcontentobject.remote_id = '$remoteCategoryID' AND ezcontentobject.id = ezcontentobject_tree.contentobject_id" );

        $parentNodeID = $parentNodeIDArray[0]['node_id'];


        $contentObjectID = $existNodeIDArray[0]['id'];
        $contentObject =& eZContentObject::fetch( $contentObjectID );

        $publishedNodeArray =& $db->arrayQuery( "SELECT parent_node_id FROM ezcontentobject_tree
                                                 WHERE contentobject_id = '$contentObjectID' AND contentobject_version = 1" );

        foreach ( $publishedNodeArray as $publishedNode )
        {
            $publishedParentNodeID = $publishedNode['parent_node_id'];
            if ( $parentNodeID == $publishedParentNodeID )
                $isPublished = true;
        }

        if ( !$isPublished )
        {
            $nodeAssignment =& eZNodeAssignment::create( array(
                                                             'contentobject_id' => $contentObject->attribute( 'id' ),
                                                             'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                             'parent_node' => $parentNodeID,
                                                             'sort_field' => 2,
                                                             'sort_order' => 0,
                                                             'is_main' => 1
                                                             )
                                                         );
            $nodeAssignment->store();
        }

        $version =& $contentObject->version( 1 );
        $contentObjectAttributes =& $version->contentObjectAttributes();
        // only update content.
        include_once( 'ez22to30xmlimport.php' );
        $contents = $articleContent;
        $xml = new eZXML();
        // replace \n with <br />
        $contents = str_replace( "\r\n\r\n\r\n\r\n", "<p /><p />", $contents );
        $contents = str_replace( "\n\n\n\n", "<p /><p />", $contents );
        $contents = str_replace( "\n\n", "<p />", $contents );
        $contents = str_replace( "\r\n\r\n", "<p />", $contents );
        $contents = str_replace( "\r\n", "<br />", $contents );
        $contents = str_replace( "\n", "<linebreak />", $contents );
        $contents = str_replace( "&lt;br&gt;", "<literalbr />", $contents );
        $contents = str_replace( "&lt;BR&gt;", "<literalbr />", $contents );
        $contents = str_replace( "&lt;p&gt;", "<literalp />", $contents );
        $contents = str_replace( "&lt;P&gt;", "<literalp />", $contents );

        if ( !in_array( $articleID,  $badArticleIDList ) )
        {
            $inputDOM = $xml->domTree( $contents, array( 'CharsetConversion' => false ) );

            $converter = new eZ22To30XMLImport( $articleID );
            $inputText = $converter->convertTo22InputXML( $inputDOM );

            $introData = "<section>";
            $introData .= "<paragraph>";
            $introData .= $inputText[0];
            $introData .= "</paragraph>";
            $introData .= "</section>";

            include_once( "kernel/classes/datatypes/ezxmltext/handlers/input/ezsimplifiedxmlinput.php" );
            $dumpdata = "";
            $simplifiedXMLInput = new eZSimplifiedXMLInput( $dumpdata, null, null );

            $introData = $simplifiedXMLInput->convertInput( $introData );
            $intro = $introData[0]->toString();
            $intro = preg_replace( "#<paragraph> </paragraph>#", "<paragraph>&nbsp;</paragraph>", $intro );
            $intro = str_replace ( "<paragraph />" , "", $intro );
            $intro = str_replace ( "<line />" , "", $intro );
            $intro = str_replace ( "<paragraph></paragraph>" , "", $intro );
            $intro = preg_replace( "#<paragraph>&nbsp;</paragraph>#", "<paragraph />", $intro );
            $intro = preg_replace( "#<paragraph></paragraph>#", "", $intro );

            $intro = preg_replace( "#[\n]+#", "", $intro );
            $intro = preg_replace( "#&lt;/line&gt;#", "\n", $intro );
            $intro = preg_replace( "#&lt;paragraph&gt;#", "\n\n", $intro );
            $contentObjectAttributes[1]->setAttribute( 'data_text', $intro );
            $contentObjectAttributes[1]->store();

            $bodyData = "<section>";
            $bodyData .= "<paragraph>";
            $bodyData .= $inputText[1];
            $bodyData .= "</paragraph>";
            $bodyData .= "</section>";
            $bodyData = $simplifiedXMLInput->convertInput( $bodyData );
            $body = $bodyData[0]->toString();
            $body = preg_replace( "#<paragraph> </paragraph>#", "<paragraph>&nbsp;</paragraph>", $body );
            $body = str_replace ( "<paragraph />" , "", $body );
            $body = str_replace ( "<line />" , "", $body );
            $body = str_replace ( "<paragraph></paragraph>" , "", $body );
            $body = preg_replace( "#<paragraph>&nbsp;</paragraph>#", "<paragraph />", $body );
            $body = preg_replace( "#<paragraph></paragraph>#", "", $body );

            $body = preg_replace( "#[\n]+#", "", $body );
            $body = preg_replace( "#&lt;/line&gt;#", "\n", $body );
            $body = preg_replace( "#&lt;paragraph&gt;#", "\n\n", $body );
            $contentObjectAttributes[2]->setAttribute( 'data_text', $body );
            $contentObjectAttributes[2]->store();
        }
        if ( !$isPublished )
        {
            $contentObject->setAttribute('remote_id', $remoteID );
            include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
            $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $contentObjectID,
                                                                                         'version' => 1 ) );
        }
    }
}
?>
