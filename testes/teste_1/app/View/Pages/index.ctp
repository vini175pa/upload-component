<div class="row">
	<?php echo $this->Form->create('upload', array(
		"inputDefaults" => array(
				"class" => 'form-control',
				'label' => false
			),
		'url' => array('controller' => 'pages', 'action' => 'upload'),
		'type' => 'file'
	)) ?>	

	<div class="col-md-3" style="margin:30px">
		<?php echo $this->Form->input('file', array(
			'type' => 'file'
		)); ?>

		<?php echo $this->Form->end(array(
			"label" => "Upload",
			'class' => 'btn btn-default	col-md-12',
			'style' => 'margin-top:10px'
		)) ?>
	</div>
</div>