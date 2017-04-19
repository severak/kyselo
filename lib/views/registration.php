<?= $form->open()->addClass("pure-form pure-form-stacked"); ?>

<label>Username</label>
<?= $form->text('username')->required(); ?>
<?= $form->showError('username'); ?>


<label>E-mail</label>
<?= $form->email('email')->required(); ?>
<?= $form->showError('email'); ?>


<label>Password</label>
<?= $form->password('password')->required(); ?>
<?= $form->showError('password'); ?>

<?= $form->submit('Register')->addClass('pure-button pure-button-primary'); ?>
    
<?= $form->close(); ?>