<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('meeting/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>> 
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="1">
          <?php if (!$form->getObject()->isNew()): ?>
            &nbsp;<?php echo link_to('Effacer', 'meeting/delete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Voulez-vous vraiment supprimer ce rendez-vous?')) ?>
          <?php endif; ?>
          <input type="submit" value="<?php echo $sf_user->getAttribute('new') ? 'Créer' : 'Modifier' ?> le rendez-vous" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form['_csrf_token']->render() ?>
      <?php //foreach($form as $widget): ?>
        <?php //if ($widget->getName() == '_csrf_token') continue ; ?>
<!--        <tr><th><?php // echo $widget->renderLabel() ?></th><td><?php // echo $widget->renderError() ?> <?php // echo $widget->render() ?> 
        <?php // if ($widget->getName() == 'input_date_1'): ?>
          Commentaire <span class="mini_help">(optionnel)</span> : <input type="text" name="meeting[input_date_1_comment]" id="meeting_input_date_1_comment" />
        <?php // endif ; ?>
        </td></tr> -->
      <?php // endforeach; ?>
      <?php echo $form ?>
    </tbody>
  </table>
</form>