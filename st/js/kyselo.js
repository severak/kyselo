Zepto(function(){
	// loading of full videos
	$('.kyselo-play-video').on('click', function(e){
		var videoElem = $(e.target).closest('.kyselo-video');
		videoElem.load('/act/iframe/'+videoElem.data('id'));
		return false;
	});

	// medium editor
	if ($('textarea.kyselo-editor').length) {
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
	}


	// NSFW switch
	$('#kyselo_nsfw_switch').on('click', function(){
		$.ajax({
			'url':'/act/toggle_nsfw',
			'data':{},
			'success': function(data){
				if (data.show_nsfw) {
					$(document.body).removeClass('kyselo-hide-nsfw');
					$('#kyselo_nsfw_switch i').attr('class', 'fa fa-eye');
				} else {
					$(document.body).addClass('kyselo-hide-nsfw');
					$('#kyselo_nsfw_switch i').attr('class', 'fa fa-eye-slash');
				}
			}
		})
		return false;
	});

	$('.kyselo-repost').on('click', function(ev){
		var target = $(ev.target);
		if (target.is('img')) {
			target = target.parent('a');
		}
		if (!target.attr('disabled')) {
			target.attr('disabled', 'disabled');
			$.ajax({
				'url':target.attr('href'),
				'success': function(data){
					target.addClass('kyselo-hidden');
				},
				'error': function () {
					alert('Repost failed!');
				}
			});
		}
		return false;
	});

	$('#new_post').on('click', function(){
		$('#post_types').show();
		return false;
	});


	$('.comment-post-button').on('click', function (ev) {
		var target = $(ev.target);
		var form = target.parents('.comment-post-form');
		var textarea = form.find('textarea');
		if (textarea.val()) {
			form.addClass('kyselo-hidden');
			$.ajax({
				'url': '/act/comment',
				'type': 'POST',
				'data': {
					text: textarea.val(),
					post_id: form.attr('data-post-id')
				},
				'success': function(data){
					form.empty();
					form.html(data);
					form.removeClass('kyselo-hidden');
				},
				'error': function () {
					form.removeClass('kyselo-hidden');
					textarea.addClass('is-danger');
				}
			});
		} else {
			textarea.addClass('is-danger');
		}
		return false;
	});


	// Check for click events on the navbar burger icon
		$(".navbar-burger").click(function() {

			// Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
			$(".navbar-burger").toggleClass("is-active");
			$(".navbar-menu").toggleClass("is-active");

		});

		$('.dropdown-trigger').on('click', function(evt){
			$(evt.target).parents('.dropdown').toggleClass("is-active");
		});

	$('[data-delete-comment]').on('click', function (evt) {
		var idComment = $(evt.target).attr('data-delete-comment') ? $(evt.target).attr('data-delete-comment') : $(evt.target).parents('.button').attr('data-delete-comment');
		console.log('smazeme ' + idComment);

		$.ajax({
			'url': '/act/comment/delete/'+idComment,
			'type': 'POST',
			'data': {
				command: 'delete'
			},
			'success': function(data){
				$('#comment' + idComment).addClass('kyselo-hidden');
			},
			'error': function () {
				// TODO - vztekající se tlačítko?
			}
		});
	});
});


