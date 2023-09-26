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

	// reposting
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

	// showing new post buttons
	$('#new_post').on('click', function(){
		$('#post_types').show();
		return false;
	});

	// making new comment
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

	// deleting comment
	$('[data-delete-comment]').on('click', function (evt) {
		if (confirm('Are you really want to delete this comment?')) {
			var idComment = $(evt.target).attr('data-delete-comment') ? $(evt.target).attr('data-delete-comment') : $(evt.target).parents('.button').attr('data-delete-comment');

			$.ajax({
				'url': '/act/comment/delete',
				'type': 'POST',
				'data': {
					id: idComment
				},
				'success': function(data){
					$('#comment' + idComment).hide();
				},
				'error': function () {
					alert('Error while deleting this comment.');
				}
			});
		}
	});

	// editing comment - step 1
	$('[data-edit-comment]').on('click', function (evt) {
		var idComment = $(evt.target).attr('data-edit-comment') ? $(evt.target).attr('data-edit-comment') : $(evt.target).parents('.button').attr('data-edit-comment');

		$.ajax({
			'url': '/act/comment/edit/' + idComment,
			'type': 'GET',
			'success': function(data){
				$('#comment' + idComment).html(data).removeClass('media');
			},
			'error': function () {
				alert('Error while editing this comment.');
			}
		});
	});

	// editing comment - step 2
	window.commentEdit = function(idComment) {
		var textarea = $('#comment' + idComment + ' textarea');
		$.ajax({
			'url': '/act/comment/edit/' + idComment,
			'type': 'POST',
			'data':{
				text: textarea.val()
			},
			'success': function(data){
				$('#comment' + idComment).html(data).addClass('media');
			},
			'error': function () {
				alert('Error while editing this comment.');
			}
		});
		return false;
	};

	// mention someone in new comment
	$('[data-mention]').on('click', function (evt) {
		var mention = $(evt.target).attr('data-mention') ? $(evt.target).attr('data-mention') : $(evt.target).parents('.button').attr('data-mention');
		var textarea = $(evt.target).parents('.comments').find('textarea');
		textarea.val('@' + mention + ' ' + textarea.val());
		textarea.focus();
	});

	// navbar for phones
	$(".navbar-burger").click(function() {
		$(".navbar-burger").toggleClass("is-active");
		$(".navbar-menu").toggleClass("is-active");

	});

	if (window.matchMedia && !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
		// removing is-hoverable on phones as phones don't have real hover
		$('.dropdown').removeClass('is-hoverable');
	}

	// dropdowns for phones
	$('.dropdown-trigger').on('click', function(evt){
		$(evt.target).parents('.dropdown').toggleClass("is-active");
	});

	// switches between absolute and relative date
	$('.datum').on('click', function (evt){
		var target = $(evt.target);
		var title = target.attr('title');
		var text = target.text();
		target.attr('title', text);
		target.text(title);
	});

	// fits panoramas into remaining space
	var containerWidth = $('.kyselo-container').width();
	$('.kyselo-panorama-holder').each(function (k, v){
		var holder = $(v);
		var currentParentWidth = holder.parent().width();
		holder.attr('style', 'width: '  + Math.min(containerWidth - 88, currentParentWidth - 15) + 'px');
	});

	if (window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
		// panorama scroll handler
		$('.kyselo-panorama-holder').on('wheel', function (e) {
			e.preventDefault();
			$(this).scrollLeft($(this).scrollLeft() + e.deltaY);
		});

		// TODO - drag to scroll later, it's hard to implement properly
	}
});


