/* PAGE WIDTH SWITCH - th[eZ]+jk[eZ]20051209 */

var switchLinkID = 'setwidth';
var widthElementID = ['page-width1','page-width2','page-width3','page-width4','page-width5','page-width6'];
var fixedWidth = '870px';
var dynamicMargin = '2em';
var elementLeft = 'logo';
var elementRight = 'searchbox';
var dynamicClass = 'dynamicwidth';
var fixedClass = 'fixedwidth';

var fixedSize = ( getCookie( 'width' ) == 'dynamic' )? false: true;
if ( !fixedSize )
{
    document.writeln( '<style type="text/css">@import url(\'/extension/ezprojects2007/design/ezprojects2007/stylesheets/dynamicwidth.css\');</style>' );
}

function setWidth()
{
    if (fixedSize)
    {
        setDynamicWidth();
    }
    else
    {
        setFixedWidth();
    }
    setCookie( 'width', ( fixedSize )? 'fixed': 'dynamic' );
}

function setDynamicWidth()
{
    document.getElementById(widthElementID[0]).style.position = 'static';
    document.getElementById(widthElementID[0]).className = dynamicClass;
    document.getElementById(elementLeft).style.left = dynamicMargin;
    document.getElementById(elementRight).style.right = dynamicMargin;

    for ( k=0; k < widthElementID.length; k++ )
    {
        if ( document.getElementById(widthElementID[k]) )
        {
            document.getElementById(widthElementID[k]).style.width = 'auto';
            document.getElementById(widthElementID[k]).style.marginLeft = dynamicMargin;
            document.getElementById(widthElementID[k]).style.marginRight = dynamicMargin;
        }
    }

    switchText('Fixed width');

    fixedSize = false;
}

function setFixedWidth()
{
    document.getElementById(widthElementID[0]).style.position = 'relative';
    document.getElementById(widthElementID[0]).className = fixedClass;
    document.getElementById(elementLeft).style.left = '0';
    document.getElementById(elementRight).style.right = '0';

    for ( k=0; k < widthElementID.length; k++ )
    {
        if ( document.getElementById(widthElementID[k]) ) { 
        document.getElementById(widthElementID[k]).style.width = fixedWidth;
        document.getElementById(widthElementID[k]).style.marginLeft = 'auto';
        document.getElementById(widthElementID[k]).style.marginRight = 'auto';
        }
    }

    switchText('Dynamic width');

    fixedSize = true;
}

function switchText( textString )
{
    document.getElementById(switchLinkID).replaceChild(document.createTextNode(textString), document.getElementById(switchLinkID).firstChild);
}

function getCookie( name )
{
    if ( !document.cookie )
    return '';

    var cookie = document.cookie;
    firstChar = document.cookie.indexOf(name);
    if ( firstChar != -1 )
    {
        firstChar += name.length + 1;
        lastChar = cookie.indexOf( ';', firstChar );
        if ( lastChar == -1 )
            lastChar = cookie.length;
        return unescape( cookie.substring( firstChar, lastChar ) );
    }
    return '';
}

function setCookie( name, value )
{
    var expiration = new Date();
    expiration.setTime( expiration.getTime() + 10000 * 60 * 60 * 24 * 365 );
    document.cookie = name + '=' + escape( value ) + '; expires=' + expiration.toGMTString() + '; path=/';
}
