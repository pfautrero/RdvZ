<form action="<?php echo url_for('') ?>" method="POST">
  <table>
    <?php echo $form ?>
    <tr>
      <td colspan="2">
        <input type="submit" id="do_connection" value="<?php echo __("Se connecter"); ?>" />
        <?php if (sfConfig::get('app_authentication_type') == "bdd"): ?>
        <input type="submit" id="show_create_form" value="<?php echo __("Créer un compte"); ?>" />
        <input style="display:none;" type="submit" id="create_account" name="create_account" value="<?php echo __("Créer le compte"); ?>" />
        <?php endif; ?>
      </td>
    </tr>
  </table>
  <?php if (sfConfig::get('app_authentication_type') == "bdd"): ?>
  <div><a  id="forgot" style="color:#123456;" href=""><?php echo __("mot de passe oublié"); ?></a></div>
  <div id="recover" style=display:none;"><?php echo __("Saisissez votre adresse mail : "); ?><input type="text" name="email_for_new_pass" size="20"><input type="submit" name="ask_mail" value="<?php echo __("envoyer"); ?>"></div>
  <?php endif; ?>
</form>

<script>

        $("#login_firstname").hide();
	$("label[for=login_firstname]").hide();

	$("#login_lastname").hide();
	$("label[for=login_lastname]").hide();

	$("#login_email").hide();
	$("label[for=login_email]").hide();
<?php if (sfConfig::get('app_authentication_type') == "bdd"): ?>
	$('#forgot').click(function(ev){
            ev.preventDefault();
            if ($(".flash_error").length != 0) {
                $(".flash_error").html("");
            }
            $('#recover').show();
	});
	$('#show_create_form').click(function(ev){
                if ($(".flash_error").length != 0) {
                    $(".flash_error").html("");
                }
		$('#recover').hide();
		ev.preventDefault();
		$("#forgot").hide();
		$("#do_connection").hide();
		$("#show_create_form").hide();
		$("#create_account").show();
		$("#login_password").hide();
		$("label[for=login_password]").hide();
		$("#login_firstname").show();
		$("label[for=login_firstname]").show();
		$("#login_lastname").show();
		$("label[for=login_lastname]").show();
		$("#login_email").show();
		$("label[for=login_email]").show();
	});
<?php endif; ?>
</script>
