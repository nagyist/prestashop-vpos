{capture name=path}{l s='Error' mod='icepay'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

{literal}
<script type="text/javascript">
jQuery(function($) { $.extend({
    form: function(url, data, method, target) {
        if (method == null) method = 'POST';
        if (data == null) data = {};

        var form = $('<form>').attr({
        	id: 'test',
            method: method,
            action: url
         }).css({
            display: 'none'
         });
         
        //if (target != null)
		//	form.attr('target', target);

        var addData = function(name, data) {
            if ($.isArray(data)) {
                for (var i = 0; i < data.length; i++) {
                    var value = data[i];
                    addData(name + '[]', value);
                }
            } else if (typeof data === 'object') {
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        addData(name + '[' + key + ']', data[key]);
                    }
                }
            } else if (data != null) {
                form.append($('<input>').attr({
                  type: 'hidden',
                  name: String(name),
                  value: String(data)
                }));
            }
        };

        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                addData(key, data[key]);
            }
        }

        return form.appendTo('body');
    }
}); });
</script>
{/literal}

<script type="text/javascript">
	$(document).ready(function() {ldelim}		
		$.form('{$_3D_post_target}', {ldelim} {$_3D_post_data} {rdelim});//.submit();
		$("#submit_btn").on("click", function() {ldelim}
								$("#test").submit();
							{rdelim});
	{rdelim});
</script>

<h2>{l s='You are redirected to Bank 3D secure page' mod='skeleton'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<p>
	<a href="{$return}" class="button_large" title="{l s='Reorder' mod='skeleton'}">« {l s='Reorder' mod='skeleton'}</a>
	<a id="submit_btn" href="#" class="button_large" title="{l s='Submit' mod='skeleton'}">« {l s='Submit' mod='skeleton'}</a>
</p>
