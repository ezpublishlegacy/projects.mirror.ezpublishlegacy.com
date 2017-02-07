{default input_handler=$attribute.content.input}
{*
    template by esben maaløe esm@baseclass.modulweb.dk
    version 0.93
    
    17-07-2003 #fixed: Headings were not inserted - now they are
    01-07-2003 #fixed: BUG now able to handle several 
                       XML input fields on same page
    29-06-2003 #fixed: BUG 'ftp://' and 'https://' urls prepended with 'http://'
*}
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
<!--
strEnterURL         = 'Enter the URL of the link you wish to create'
strEnterLinkText    = 'Enter link text'
strLinkPopup        = 'Should this link pop up in a new window when clicked ?'
strEnterText        = 'Enter the text to be displayed as '
strEnterThe         = 'Enter the ' 
strListItem         = '. item in the list \n(press cancel or submit empty string to end list)'
strEnterHeading     = 'Enter the text of the heading'

{literal}

function XMLElement( elementName, innerXML)
{//
	this.elementName = elementName.toLowerCase()

	this.attributes = new Array()

	this.childElements = new Array()
    this.setInnerXML( innerXML || '' )

}

function objXML_setInnerXML( innerXML )
{//
    this.innerXML = innerXML || ''
}

function strRepeat(str, multiplier)
{//
    var retVal = ''
    for (var i = 0; i < multiplier ; i++)
    {
        retVal += str
    }
    return retVal
}

function objXML_toString(indentLevel, newLine)
{//
    indentLevel = indentLevel || 0
    
    newLine = newLine || 0

    var nlAfterOpenTag = (newLine & 1 ) > 0 ? '\n' : '';
    var nlAfterCloseTag = (newLine & 2 ) > 0 ? '\n' : '';
    
    var retVal = strRepeat('    ', indentLevel) +  '<' + this.elementName

    for (var i = 0; i < this.attributes.length ; i++)
    {
        retVal += ' ' + this.attributes[i][0] + '="' + this.attributes[i][1] + '"' 
    }

    if ( this.childElements.length == 0 && this.innerXML == '' )
    {
        return retVal + ' />' + nlAfterCloseTag
    }

    retVal += '>' + nlAfterOpenTag + this.innerXML 
    
    for (var i = 0; i < this.childElements.length ; i++)
    {
        retVal += this.childElements[i].toString(indentLevel + 1, 2 )
    }
    
    
    return retVal + '</' + this.elementName + '>' + nlAfterCloseTag
}


function objXML_addAttribute(name, value)
{//
    name = name.toLowerCase()
    this.attributes[this.attributes.length] = new Array(name, value)
    if ( name == 'name'  )
    {
        this.attributes[this.attributes.length] = new Array('id', value)
    }
}

function objXML_addChild( element )
{//
    if ( typeof(element) == 'object' )
    {
        this.childElements[this.childElements.length] = element
    }
    else
    {
        this.childElements[this.childElements.length] = new XMLElement(element)
    }
    return this.childElements.length - 1
}

XMLElement.prototype.addAttribute   = objXML_addAttribute
XMLElement.prototype.toString       = objXML_toString
XMLElement.prototype.addChild       = objXML_addChild
XMLElement.prototype.setInnerXML    = objXML_setInnerXML




function getBold( txtArea )
{//
    return doSimpleTag(txtArea, 'strong', 'bold')
}

function getItalic( txtArea )
{//
    return doSimpleTag(txtArea, 'emphasize', 'italic')
}

function getLink( txtArea )
{//
    var url = 'http://'
    var urlPrompt = strEnterURL
    var errMsg = ''
    var isDirty = false
    
    while( !validateURL( url ) )
    {
        if ( isDirty )
        {
            errMsg = '*****   ERROR: URL is not valid    ****\n'
        }
        
        url = getValue( errMsg + urlPrompt, url )

        if ( !url )
        {
            return false
        }

        isDirty = true
    }

    var linkText = getValue( strEnterLinkText, url)
    if ( !linkText )
    {
        linkText = url
    }
    
    if ( 
            !url.indexOf( 'http://' ) == 0 
            && !url.indexOf( 'https://' ) == 0 
            && !url.indexOf( 'ftp://' ) == 0 
            
        )
    {
        url = 'http://' + url
    }
    
    var target = confirm( strLinkPopup )

    var xmlTag = new XMLElement( 'link', linkText )

    xmlTag.addAttribute( 'href', url)

    xmlTag.addAttribute( 'target', target ? '_blank' : '_self' )
    
    addText( txtArea, xmlTag.toString(0) )
}

function validateURL( url )
{//

    var urlRgx = /(http:\/\/|https:\/\/|ftp:\/\/)?([a-zA-Z0-9][a-zA-Z0-9-]*\.)+([a-z]{2,3})(:\d{2,5})?(\/[a-zA-Z\/.0-9_]*)?(\?[=a-zA-Z+%&0-9._#]*)?/
    return urlRgx.test( url )
}


function doSimpleTag( txtArea, tag, screenName )
{//
    screenName = screenName || tag
    
    retVal = getValue( strEnterText + screenName )

    if ( retVal )
    {
        var xmlTag = new XMLElement( tag, retVal )
        addText( txtArea, xmlTag.toString())
    }
}

function getValue( question, defVal )
{//
    question = question || ''
    defVal = defVal || ''
    
    return prompt( question, defVal )
}

function getList( txtArea, type )
{//
    var listItems = new Array()
    var count = 1
    type = type || 'ol'
    
    while( listItems.length == 0 || listItems[listItems.length - 1 ] != '' )
    {
        listItems[listItems.length] = prompt( strEnterThe + count + strListItem, '' )
        
        if ( !listItems[listItems.length - 1] )
        {
            listItems[listItems.length - 1] = ''
        }
        
        count++ 
    }

    if ( listItems.length == 1 )
    {
        return
    }
    
    var XMLList = new XMLElement( type.toLowerCase() == 'ol' ? 'ol' : 'ul' )
    
    for (var i = 0; i < listItems.length - 1 ; i++)
    {
        listItems[i] = new XMLElement( 'li', listItems[i] )
        XMLList.addChild( listItems[i] )
    }
    
    addText( txtArea, XMLList.toString( 0, true ) )
}

function getHeading( txtArea, level )
{//
    var txt = getValue( strEnterHeading, '')

    level = level || 0
    
    if ( !txt )
    {
        return
    }
    
    var XMLTag = new XMLElement('header', txt )
    
    if ( level > 0 )
    {
        XMLTag.addAttribute( 'level', level )
    }
    
    addText( txtArea, XMLTag.toString(0, 2) )
}

function addText( txtArea, txt )
{//
    {/literal}
    txtArea.value += txt
    txtArea.focus()
    {literal}
    
}



{/literal}

//-->
</SCRIPT>
<!-- DHTML editor textarea field -->
<p /><input type="Button" class="button" value="{"Bold"|i18n}" name="cmdBold_{$attribute.id}" onclick="getBold( this.form.ContentObjectAttribute_data_text_{$attribute.id} )" style="width : 57px" /><input type="Button" class="button" value="{"Italic"|i18n}" name="cmdItalic_{$attribute.id}" onclick="getItalic( this.form.ContentObjectAttribute_data_text_{$attribute.id} )" style="width : 57px" /><input type="Button" class="button" value="{"Link"|i18n}" name="cmdLink_{$attribute.id}" onclick="getLink( this.form.ContentObjectAttribute_data_text_{$attribute.id} )" style="width : 57px" /><input type="Button" class="button" value="{"List"|i18n}" name="cmdList_{$attribute.id}" style="width : 57px" onclick="getList( this.form.ContentObjectAttribute_data_text_{$attribute.id}, 'ul' )" /><input type="Button" class="button" value="{"Num. list"|i18n}" name="cmdNumlist_{$attribute.id}" style="width : 57px" onclick="getList( this.form.ContentObjectAttribute_data_text_{$attribute.id}, 'ol' )" />
<select name="slcHeading_{$attribute.id}">
    <option value="1">{"Heading 1"|i18n}</option>
    <option value="2">{"Heading 2"|i18n}</option>
    <option value="3">{"Heading 3"|i18n}</option>
    <option value="4">{"Heading 4"|i18n}</option>
    <option value="5">{"Heading 5"|i18n}</option>
    <option value="6">{"Heading 6"|i18n}</option>
</select>
<input type="Button" class="button" value="{"Add"|i18n}" name="cmdAddHeading_{$attribute.id}" onclick="getHeading( this.form.ContentObjectAttribute_data_text_{$attribute.id}, this.form.slcHeading_{$attribute.id}.options[this.form.slcHeading_{$attribute.id}.selectedIndex].value )" style="width : 57px" /></p>
  <textarea class="box" name="ContentObjectAttribute_data_text_{$attribute.id}" cols="97" rows="{$attribute.contentclass_attribute.data_int1}">{$input_handler.input_xml}</textarea>
<!-- End editor -->
{/default}
