{* Folder admin view template *}

{default with_children=true()
         is_editable=true()
         is_standalone=true()}
{let page_limit=15
     list_count=and( $with_children, fetch( content, list_count, hash( parent_node_id, $node.node_id ) ) )}
{default content_object=$node.object
         content_version=$node.contentobject_version_object
         node_name=$node.name}

{section show=$is_standalone}
    <form method="post" action={"content/action"|ezurl}>
{/section}


<div class="objectheader">
    <h2>User group</h2>
</div>

<div class="object">
    <h1>{$node_name|wash( xhtml )}</h1>

    <input type="hidden" name="TopLevelNode" value="{$content_object.main_node_id}" />

    <p>{attribute_view_gui attribute=$node.object.data_map.description}</p>

    <div class="buttonblock">
    {section show=$is_editable}
        {switch match=$content_object.can_edit}
        {case match=1}
            <input type="hidden" name="ContentObjectID" value="{$content_object.id}" />
            <input class="button" type="submit" name="EditButton" value="{'Edit'|i18n( 'design/standard/node/view' )}" />
        {/case}
        {case match=0}
        {/case}
        {/switch}
    {/section}
    </div>
</div>


{let name=Object related_objects=$content_version.related_contentobject_array}
{section name=ContentObject  loop=$Object:related_objects show=$Object:related_objects  sequence=array( bglight, bgdark )}
    <div class="block">
        {content_view_gui view=text_linked content_object=$Object:ContentObject:item}
    </div>
{section-else}
{/section}
{/let}

{section show=$is_standalone}
    {section name=ContentAction loop=$content_object.content_action_list show=$content_object.content_action_list}
        <div class="block">
            <input type="submit" name="{$ContentAction:item.action}" value="{$ContentAction:item.name|wash}" />
        </div>
    {/section}
{/section}

<h2>Users</h2>

{section show=$with_children}
    {let name=Child
        children=fetch( content, list, hash( parent_node_id, $node.node_id,
                                             sort_by, $node.sort_array,     
                                             limit, $page_limit, 
                                             offset, $view_parameters.offset ) )
        can_remove=false() 
        can_edit=false() 
        can_create=false() 
        can_copy=false()}

    {section show=$:children}

        {section loop=$:children}
            {section show=$:item.object.can_remove}
                {set can_remove=true()}
            {/section}
            {section show=$:item.object.can_edit}
                {set can_edit=true()}
            {/section}
            {section show=$:item.object.can_create}
                {set can_create=true()}
            {/section}
        {/section}

        {set can_copy=$content_object.can_create}



        <table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            {section show=$:can_remove}
                <th width="1">&nbsp;
                    
                </th>
            {/section}
            <th>
                {"Name"|i18n("design/standard/node/view")}
            </th>
            <th>
                {"Class"|i18n("design/standard/node/view")}
            </th>
            {section show=eq( $node.sort_array[0][0], 'priority' )}
                <th>
                    {"Priority"|i18n( "design/standard/node/view" )}
                </th>
            {/section}
            {section show=$:can_edit}
                <th width="1">
                    {"Edit"|i18n("design/standard/node/view")}
                </th>
            {/section}
            {section show=$:can_copy}
                <th width="1">
                    {"Copy"|i18n("design/standard/node/view")}
                </th>
            {/section}
        </tr>
        {section loop=$:children sequence=array( bglight, bgdark )}
            <tr class="{$Child:sequence}">
                {section show=$:can_remove}
                    <td align="right" width="1">
                        {section show=$:item.object.can_remove}
                            <input type="checkbox" name="DeleteIDArray[]" value="{$Child:item.node_id}" />
                        {/section}
                    </td>
                {/section}
                <td>
		        <a href={concat('content/view/full/',$Child:item.node_id)|ezurl}>
			<a href={concat('content/view/full/',$Child:item.node_id)|ezurl}>
			{switch match=$Child:item.object.contentclass_id}
			{case match=4}
			    <img src={"user.gif"|ezimage} border="0" alt="{'User'|i18n('design/standard/node/view')}" />
			{/case}
			{case match=3}
			     <img src={"usergroup.gif"|ezimage} border="0" alt="{'User group'|i18n('design/standard/node/view')}" />
			{/case}
			{case}
			    <img src={"class_2.png"|ezimage} border="0" alt="{'Document'|i18n('design/standard/node/view')}" />
			{/case}
			{/switch}
			&nbsp;
			{$Child:item.name|wash}</a></a>
                </td>
                <td>
                    {$Child:item.object.class_name|wash}
                </td>
                {section show=eq( $node.sort_array[0][0], 'priority' )}
                    <td width="40" align="left">
                        <input type="text" name="Priority[]" size="2" value="{$Child:item.priority}">
                        <input type="hidden" name="PriorityID[]" value="{$Child:item.node_id}">
                    </td>
                {/section}

                {section show=$:can_edit}
                    <td width="1">
                        {section show=$:item.object.can_edit}
                            <a href={concat( "content/edit/", $Child:item.contentobject_id )|ezurl}><img src={"edit.png"|ezimage} alt="Edit" /></a>
                        {/section}
                    </td>
                {/section}
                {section show=$:can_copy}
                    <td>
                        <a href={concat( "content/copy/", $Child:item.contentobject_id )|ezurl}><img src={"copy.gif"|ezimage} alt="{'Copy'|i18n( 'design/standard/node/view' )}" /></a>
                    </td>
                {/section}

            </tr>
        {/section}
        </table>

        {section show=eq( $node.sort_array[0][0], 'priority' )}
            {section show=and( $content_object.can_edit,eq( $node.sort_array[0][0], 'priority' ) )}
                 <input class="button" type="submit"  name="UpdatePriorityButton" value="{'Update'|i18n('design/standard/node/view')}" />
            {/section}
        {/section}
        {section show=$:can_edit}
        {/section}
        {section show=$:can_copy}
        {/section}
        {section show=$:can_remove}
            {section show=fetch(content, list, hash(
                                               parent_node_id, $node.node_id,
                                               sort_by, $node.sort_array,
                                               limit, $page_limit,
                                               offset, $view_parameters.offset ) )}
                <input type="submit" name="RemoveButton" value="Remove" />
            {/section}
        {/section}
    {/section}
    {/let}

    {include name=navigator
             uri='design:navigator/google.tpl'
             page_uri=concat('/content/view','/full/',$node.node_id)
             item_count=$list_count
             view_parameters=$view_parameters
             item_limit=$page_limit}


{/section}


{section show=$is_standalone}
    </form>
{/section}
{let user_class_group_id=ezini('UserSettings','UserClassGroupID')
     user_class_list_allowed=fetch('content','can_instantiate_classes',hash(parent_node,$node))
     user_class_list=fetch('content','can_instantiate_class_list',hash(group_id,$user_class_group_id,parent_node,$node))}
{section show=$user_class_list_allowed}
<div class="buttonblock">
    <form method="post" action={"content/action"|ezurl}>
         <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
         <input type="hidden" name="NodeID" value="{$node.node_id}" />
         <select name="ClassID" class="create">
	      {section name=Classes loop=$user_class_list}
	      <option value="{$:item.id}">{$:item.name|wash}</option>
	      {/section}
         </select>
         <input class="button" type="submit" name="NewButton" value="{'Create here'|i18n('design/standard/node/view')}" />
    </form>
</div>
{/section}
{/let}
{/default}
{/let}
{/default}
