Zepto(function(){
	// loading of full videos
	$('.kyselo-play-video').on('click', function(e){
		var videoElem = $(e.target).closest('.kyselo-video');
		videoElem.load('/act/iframe/'+videoElem.data('id'));
		return false;
	});
	
	new MediumEditor("textarea.kyselo-editor", {
		buttonLabels:"fontawesome", 
		placeholder:{text:"text..."}, 
		paste:{forcePlainText:false}, 
		autoLink: true, 
		toolbar: {
			buttons: ["bold", "italic", "anchor", "quote", "pre", "unorderedlist","orderedlist"]
		}
	});
	
	console.log('kyselo javascripts OK');
});