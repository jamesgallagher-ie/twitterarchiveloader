<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='twitterarchiveloader'}</span>
    <h1>
        <img src="{$site_root_path}plugins/twitterarchiveloader/assets/img/plugin_icon.png" class="plugin-image">
        TwitterArchiveLoader Plugin
    </h1>

    <p>{$message}</p>

</div>
{include file="_usermessage.tpl" enable_bootstrap=1}

<table class="table">
	<tr>
		<th><h4 class="pull-left">Account</h4></th>
		<th><h4 class="pull-left">Twitter Count</h4></th>
		<th><h4 class="pull-left">ThinkUp Count</h4></th>
		<th><h4 class="pull-left">Archive file</h4></th>
	</tr>
	
	{* Now loop over the instances to get the twitter user, twitter's count of tweets and ThinkUp's count of tweets *}
	{foreach from=$owner_instances key=iid item=i name=foo}
	<tr>
		<td>
			<h5 class="lead"><i class="icon-twitter icon-muted"></i>&nbsp;<a href="https://twitter.com/intent/user?screen_name={$i->network_username}">@{$i->network_username}</a></h5>
		</td>
		<td>
			<h5>{$i->total_posts_by_owner}</h5>
		</td>
		<td>
			<h5>{$i->total_posts_in_system}</h5>
		</td>
		<td>
			 <form name="{$i->network_username|regex_replace:"/@/":""}_tweet_archive-form" id="{$i->network_username|regex_replace:"/@/":""}_tweet_archive-form" class="form-horizontal" method="post" enctype="multipart/form-data" action="{$site_root_path}plugins/twitterarchiveloader/archive-uploader.php">                 
                    <div style="margin-top: 12px; margin-bottom: 12px; margin-right: 20px; float: left;">
                        <input type="file" name="tweet_archive"  id="{$i->network_username|regex_replace:"/@/":""}_tweet_archive" />
						<input type="hidden" name="twitter_username" id="{$i->network_username|regex_replace:"/@/":""}">
                    </div>

                    <div style="margin-top: 12px; margin-bottom: 12px; margin-right: 20px; float: left;">
                        <input type="submit" id="upload-{$i->network_username|regex_replace:"/@/":""}_tweet_archive-submit" name="Submit" class="btn btn-small btn-disabled" value="Upload">
                        <span class="icon-2x icon-spinner icon-spin" id="uploading-{$i->network_username|regex_replace:"/@/":""}_tweet_archive-status" style="display: none;"></span>
                    </div>   
             </form>
		</td>
	</tr>
	{/foreach}
</table>
<script type="text/javascript">
    {literal}

    (function(e){"use strict";e.fn.filestyle=function(t){if(typeof t=="object"||typeof t=="undefined"){var n={buttonText:"Choose file",textField:!0,icon:!1,classButton:"",classText:"",classIcon:"icon-folder-open"};return t=e.extend(n,t),this.each(function(){var n=e(this);n.data("filestyle",!0),n.css({position:"fixed",top:"-100px",left:"-100px"}).parent().addClass("form-search").append((t.textField?'<input type="text" class="'+t.classText+'" disabled size="40" /> ':"")+'<button type="button" class="btn '+t.classButton+'" >'+(t.icon?'<i class="'+t.classIcon+'"></i> ':"")+t.buttonText+"</button>"),n.change(function(){n.parent().children(":text").val(e(this).val())}),n.parent().children(":button").click(function(){n.click()})})}return this.each(function(){var n=e(this);n.data("filestyle")===!0&&t==="clear"?(n.parent().children(":text").val(""),n.val("")):window.console.error("Method filestyle not defined!")})}})(jQuery);
	{/literal}
	{foreach from=$owner_instances key=iid item=i name=foo}
    $('#{$i->network_username|regex_replace:"/@/":""}_tweet_archive').filestyle({literal}{
        buttonText: 'Select Archive',
        classButton: 'btn-primary btn-small',
        textField: false,
        icon: true,
        classIcon: 'icon-upload-alt icon-white'
    }{/literal});
    $('#{$i->network_username|regex_replace:"/@/":""}_tweet_archive').click( function() {literal}{{/literal} $('#upload-{$i->network_username|regex_replace:"/@/":""}_tweet_archive-submit').addClass('btn-primary'); {literal}}{/literal} );
    $('#{$i->network_username|regex_replace:"/@/":""}_tweet_archive-form').submit( function() {literal}{{/literal} $('#uploading-{$i->network_username|regex_replace:"/@/":""}_tweet_archive-status').show() {literal}}{/literal} );

	{/foreach}

</script>
