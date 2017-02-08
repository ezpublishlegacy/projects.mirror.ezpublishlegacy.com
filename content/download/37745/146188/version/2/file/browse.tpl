{let item_type=ezpreference( 'admin_list_limit' )
     number_of_items=min( $item_type, 3)|choose( 10, 10, 25, 50 )
     browse_list_count=fetch( content, list_count, hash( parent_node_id, $node_id, depth, 1))
     node_array=fetch( content, list, hash( parent_node_id, $node_id, depth, 1, offset, $view_parameters.offset, limit, $number_of_items, sort_by, $main_node.sort_array ) )
     select_name='SelectedObjectIDArray'
     select_type='checkbox'
     select_attribute='contentobject_id'
     classIDList=array() }

{* Para sacar los id de las clases *}
{foreach $browse.class_array as $classIdentifier}
 {def $class=fetch('content', 'class', hash( 'class_id', $classIdentifier ))}
   {set $classIDList=$classIDList|append($class.id)}
 {undef $class}
{/foreach}

{section show=eq( $browse.return_type, 'NodeID' )}
    {set select_name='SelectedNodeIDArray'}
    {set select_attribute='node_id'}
{/section}

{section show=eq( $browse.selection, 'single' )}
    {set select_type='radio'}
{/section}

{section show=$browse.description_template}
    {include name=Description uri=$browse.description_template browse=$browse main_node=$main_node}
{section-else}

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'Browse'|i18n( 'design/admin/content/browse' )}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

<div class="block">

<p>{'To select objects, choose the appropriate radiobutton or checkbox(es), and click the "Choose" button.'|i18n( 'design/admin/content/browse' )}</p>
<p>{'To select an object that is a child of one of the displayed objects, click the object name and you will get a list of the children of the object.'|i18n( 'design/admin/content/browse' )}</p>

</div>

{* DESIGN: Content END *}</div></div></div></div></div></div>

</div>

{/section}


{* INICIO BUSQUEDA *}

{let search=false()
     search_text=false()
     search_count=false()
     search_result=false()
     search_data=false()
     stop_word_array=false()
     page_limit=10 }

{set search_text=ezhttp('SearchText', 'get')}

    {if is_array($browse.class_array)}
    {set search=fetch(content,search,
                      hash(text,$search_text,
                           section_id,$search_section_id,
                           subtree_array,$search_subtree_array,
                           sort_by,array('modified',false()),
			   class_id, $classIDList,
                           offset,$view_parameters.offset,
                           limit,$page_limit))}
    {else}
    {set search=fetch(content,search,
                      hash(text,$search_text,
                           section_id,$search_section_id,
                           subtree_array,$search_subtree_array,
                           sort_by,array('modified',false()),
                           offset,$view_parameters.offset,
                           limit,$page_limit))}
    {/if}
    {set search_result=$search['SearchResult']}
    {set search_count=$search['SearchCount']}
    {set stop_word_array=$search['StopWordArray']}
    {set search_data=$search}

<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h1 class="context-title">{'Search'|i18n( 'design/admin/content/search' )}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

<div class="context-attributes">

<form  method="get" action={'/content/browse/'|ezurl}>

<div class="block">
    <input class="halfbox" type="text" name="SearchText" id="Search" value="{$search_text|wash}" />
    <input class="button"  name="SearchButton" type="submit" value="{'Search'|i18n( 'design/admin/content/search' )}" />
    <input type="hidden" name="SearchBrowser" id="Search" value="1" />
</div>

</form>

<form method="post" action={$browse.from_page|ezurl}>

{* Excluded words. *}
{section show=$stop_word_array}
<p>
{'The following words were excluded from the search'|i18n( 'design/admin/content/search' )}:
{section name=StopWord loop=$stop_word_array}
    {$StopWord:item.word|wash}
    {delimiter}, {/delimiter}
{/section}
</p>
{/section}

{* No matches. *}
{section show=and($search_count|not, ne($search_text, '')) }
<h2>{'No results were found while searching for <%1>'|i18n( 'design/admin/content/search',, array( $search_text ) )|wash}</h2>
    <p>{'Search tips'|i18n( 'design/admin/content/search' )}</p>
    <ul>
        <li>{'Check spelling of keywords.'|i18n( 'design/admin/content/search' )}</li>
        <li>{'Try changing some keywords e.g. car instead of cars.'|i18n( 'design/admin/content/search' )}</li>
        <li>{'Try more general keywords.'|i18n( 'design/admin/content/search' )}</li>
        <li>{'Fewer keywords gives more results, try reducing keywords until you get a result.'|i18n( 'design/admin/content/search' )}</li>
    </ul>
{/section}

</div>
{* DESIGN: Content END *}</div></div></div></div></div></div>

</div>

{* Search result. *}
{section show=$search_count}
<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h2 class="context-title">{'Search for <%1> returned %2 matches'|i18n( 'design/admin/content/search',, array( $search_text, $search_count ) )|wash}</h2>

{* DESIGN: Mainline *}<div class="header-subline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">


<table class="list" cellspacing="0">

{section loop=$search_result sequence=array( bglight, bgdark )}
  <tr class="{$Nodes.sequence}">
    <td>
     <input type="{$select_type}" name="{$select_name}[]" value="{$item[$select_attribute]}" />
     {$item.object.class_identifier|class_icon( small, $item.object.class_name )}&nbsp;{$item.name|wash}
    </td>
    <td  class="class">
     {$item.object.content_class.name|wash}
    </td>
</tr>
{/section}
</table>


<div class="context-toolbar">
{include name=Navigator
         uri='design:navigator/google.tpl'
         page_uri='/content/browse'
         page_uri_suffix=concat( '?SearchText=', $search_text|urlencode, $search_timestamp|gt( 0 )|choose( '', concat( '&SearchTimestamp=', $search_timestamp ) ) )
         item_count=$search_count
         view_parameters=$view_parameters
         item_limit=$page_limit}
</div>

<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
    <input class="button" type="submit" name="SelectButton" value="{'OK'|i18n( 'design/admin/content/browse' )}" />
    <input class="button" type="submit" name="BrowseCancelButton" value="{'Cancel'|i18n( 'design/admin/content/browse' )}" />
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>

{* DESIGN: Content END *}</div></div></div></div></div></div>

</div>
{/section}
{/let}

{* FIN BUSQUEDA *}


{section show=eq($search_text, '')}

<div class="context-block">


{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

{let current_node=fetch( content, node, hash( node_id, $browse.start_node ) )}
{section show=$browse.start_node|gt( 1 )}
    <h2 class="context-title">
    <a href={concat( '/content/browse/', $main_node.parent_node_id, '/' )|ezurl}><img src={'back-button-16x16.gif'|ezimage} alt="{'Back'|i18n( 'design/admin/content/browse' )}" /></a>
    {$current_node.object.content_class.identifier|class_icon( original, $current_node.object.content_class.name|wash )}&nbsp;{$current_node.name|wash}&nbsp;[{$current_node.children_count}]</h2>
{section-else}
    <h2 class="context-title"><img src={'back-button-16x16.gif'|ezimage} alt="Back" /> {'folder'|class_icon( small, $current_node.object.content_class.name|wash )}&nbsp;{'Top level'|i18n( 'design/admin/content/browse' )}&nbsp;[{$current_node.children_count}]</h2>
{/section}
{/let}

{* DESIGN: Subline *}<div class="header-subline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

{* Items per page and view mode selector. *}
<div class="context-toolbar">
<div class="block">
<div class="left">
    <p>
    {switch match=$number_of_items}
    {case match=25}
        <a href={'/user/preferences/set/admin_list_limit/1'|ezurl}>10</a>
        <span class="current">25</span>
        <a href={'/user/preferences/set/admin_list_limit/3'|ezurl}>50</a>

        {/case}

        {case match=50}
        <a href={'/user/preferences/set/admin_list_limit/1'|ezurl}>10</a>
        <a href={'/user/preferences/set/admin_list_limit/2'|ezurl}>25</a>
        <span class="current">50</span>
        {/case}

        {case}
        <span class="current">10</span>
        <a href={'/user/preferences/set/admin_list_limit/2'|ezurl}>25</a>
        <a href={'/user/preferences/set/admin_list_limit/3'|ezurl}>50</a>
        {/case}

        {/switch}
    </p>
</div>
<div class="right">
    <p>
    {switch match=ezpreference( 'admin_children_browsemode' )}
    {case match='thumbnail'}
      <a href={'/user/preferences/set/admin_children_browsemode/list'|ezurl} title="{'Display sub items using a simple list.'|i18n( 'design/admin/content/browse' )}">{'List'|i18n( 'design/admin/content/browse' )}</a>
      <span class="current">{'Thumbnail'|i18n( 'design/admin/content/browse' )}</span>
    {/case}
    {case}
      <span class="current">{'List'|i18n( 'design/admin/content/browse' )}</span>
      <a href={'/user/preferences/set/admin_children_browsemode/thumbnail'|ezurl} title="{'Display sub items as thumbnails.'|i18n( 'design/admin/content/browse' )}">{'Thumbnail'|i18n( 'design/admin/content/browse' )}</a>
    {/case}
    {/switch}
    </p>
</div>
<div class="break"></div>
</div>
</div>


{* Display the actual list of nodes. *}
{switch match=ezpreference( 'admin_children_browsemode' )}
    {case match='thumbnail'}
        {include uri='design:content/browse_mode_thumbnail.tpl'}
    {/case}
    {case}
        {include uri='design:content/browse_mode_list.tpl'}
    {/case}
{/switch}

<div class="context-toolbar">
{include name=Navigator
         uri='design:navigator/google.tpl'
         page_uri=concat( '/content/browse/', $main_node.node_id )
         item_count=$browse_list_count
         view_parameters=$view_parameters
         item_limit=$number_of_items}
</div>


{* DESIGN: Content END *}</div></div></div>

<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
    <input class="button" type="submit" name="SelectButton" value="{'OK'|i18n( 'design/admin/content/browse' )}" />
    <input class="button" type="submit" name="BrowseCancelButton" value="{'Cancel'|i18n( 'design/admin/content/browse' )}" />
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>


</div>
{/section}
{section var=PersistentData show=$browse.persistent_data loop=$browse.persistent_data}
    <input type="hidden" name="{$PersistentData.key|wash}" value="{$PersistentData.item|wash}" />
{/section}

<input type="hidden" name="BrowseActionName" value="{$browse.action_name}" />
{section show=$browse.browse_custom_action}
    <input type="hidden" name="{$browse.browse_custom_action.name}" value="{$browse.browse_custom_action.value}" />
{/section}

{section show=$cancel_action}
<input type="hidden" name="BrowseCancelURI" value="{$cancel_action}" />
{/section}
{/let}
</form>
