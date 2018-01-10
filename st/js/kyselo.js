Zepto(function(){
	// loading of full videos
	$('.kyselo-play-video').on('click', function(e){
		var videoElem = $(e.target).closest('.kyselo-video');
		videoElem.load('/act/iframe/'+videoElem.data('id'));
		return false;
	});
	
	// medium editor
	new MediumEditor("textarea.kyselo-editor", {
		buttonLabels:"fontawesome", 
		placeholder:{text:"text..."}, 
		paste:{forcePlainText:false}, 
		autoLink: true, 
		toolbar: {
			buttons: ["bold", "italic", "anchor", "quote", "pre", "unorderedlist","orderedlist"]
		}
	});
	
	// 
	$('textarea.kyselo-editor').on('invalid', function(ev){
		var target = $(ev.target);
		var meDiv = $('#'+target.attr('medium-editor-textarea-id'));
		meDiv.addClass('invalid');
		if (!meDiv.data('error-span')) {
			meDiv.after('<span class="pure-form-message-inline kyselo-form-error">'+ev.target.validationMessage+'</span>');
			meDiv.data('error-span', 'ok');
		}	
	});
	
	console.log('kyselo javascripts OK');
});