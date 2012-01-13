<form id="{$form.name}" name="{$form.name}">

<div style="padding-left:25px; padding-right:40px;">
{include file="system_appbuilder_btn.tpl.html"}
	
	<table><tr><td>
		{if $form.icon !='' }
		<div class="form_icon"><img  src="{$form.icon}" border="0" /></div>
		{/if}
	
		<div style="float:left; width:600px;">
			<h2>
			{$form.title}
			</h2> 
			{if $form.description}
			<p class="input_row" style="line-height:20px;padding-bottom:20px;">		
			<span>{$form.description}</span>
			</p>
			{else}
			<div style="height:15px;"></div>
			{/if}
		</div>
	</td></tr></table>
	
	<div style="padding-left:30px;" >
    {if $errors}
	    <div id='errorsDiv' class='innerError errorBox'>
	    {foreach item=errMsg from=$errors}
	        <div>{$errMsg}</div>
	    {/foreach}
	    {literal}<script>setTimeout("$('errorsDiv').fade( {from: 1, to: 0});",3000);</script>{/literal}
	    </div>
	{/if}
	
	{if $notices}
	    <div id='noticeDiv' class='noticeBox' >
	    {foreach item=noticeMsg from=$notices}
	        <div>{$noticeMsg}</div>
	    {/foreach}
	    </div>
	    {literal}<script>setTimeout("$('noticeDiv').fade( {from: 1, to: 0});",3000);</script>{/literal}
	{/if}
    
    {if $actionPanel|@count > 0}
    <p class="input_row">
        {foreach item=elem from=$actionPanel}
            {$elem.element}
        {/foreach}
    </p>
    <div id='progressDiv' class='noticeBox' style='display:none'></div>
    {/if}
    
	{assign var=es_counter value=0}
	{foreach item=setname name=elemsets  from=$form.elementSets}
		{if $smarty.foreach.elemsets.first}
		<div id="element_set_{$es_counter}" class="underline upline">
		{else}
		<div id="element_set_{$es_counter}" class="underline">
		{/if}
		<h2 class="element_set_title"><a id="element_set_btn_{$es_counter}" class="shrink" href="javascript:;" onclick="switch_elementset('{$form.name}','{$es_counter}')" >{$setname}</a></h2>
			<div id="element_set_panel_{$es_counter}" class="element_set_panel">
		{assign var=es_elem_counter value=0}
		{foreach item=item key=itemName from=$dataPanel}
			{if $item.elementset eq $setname}
			{if $item.type eq 'CKEditor' or $item.type eq 'RichText' 
				or $item.type eq 'Textarea' or $item.type eq 'LabelTextarea'
				or $item.type eq 'LabelTextarea' or $item.type eq 'LabelImage'
				 or $item.type eq 'RawData'
				 or $item.type eq 'IDCardReader'
			}
				<table  id="{$itemName}_container" class="input_row">
				<tr>
				<td style="width:80px;">	
					<label style="text-align:left">{$item.label}</label>
				</td>
				<td>
					{if $errors.$itemName}
					<span class="input_error_msg" style="width:240px;">{$errors.$itemName}</span>
					{elseif $item.description}
					<span class="input_desc" style="width:240px;">{$item.description}</span>			
					{/if}
				</td>
				</tr>
				<tr><td colspan="2" align="center" >
					<span class="label_textarea" style="{if $item.width}width:{$item.width+15}px;{else}{if $default_label_textarea_width}{$default_label_textarea_width}{else}width:655px;{/if}{/if}">{$item.element}</span>
								
				</td></tr>
				</table>		
			{else}
				{if $item.type eq 'Hidden' }
				<table  id="{$itemName}_container" class="input_row" style="display:none">
				{else}
				<table  id="{$itemName}_container" class="input_row">
				{/if}					
				<tr>
				<td >	
					<label style="text-align:left">{$item.label}</label>
				</td>
				<td>
				{if $item.type eq 'Checkbox' }
					<span class="label_text" >{$item.element} {$item.description}</span>
				{else}
					<span class="label_text" style="float:left;{if $item.width}width:{$item.width}px;{else}{if $default_label_text_width}{$default_label_text_width}{else}width:260px;{/if}{/if}">{$item.element}</span>
					{if $errors.$itemName}
					<span class="input_error_msg" style="width:240px;">{$errors.$itemName}</span>
					{elseif $item.description}
					<span class="input_desc" style="width:240px;">{$item.description}</span>			
					{/if}				
				{/if}					
					</td>
				</tr>
				</table>
			{/if}
			{assign var=es_elem_counter value=$es_elem_counter+1}					
			{/if}
		{/foreach}
			</div>
		{if $es_elem_counter eq '0'}
			<script>$('element_set_{$es_counter}').hide();</script>
		{/if}			
		</div>
		<script>
			init_elementset('{$form.name}','{$es_counter}');
		</script>
	{assign var=es_counter value=$es_counter+1}			
	{/foreach}

	</div>
	
</div>
</form>