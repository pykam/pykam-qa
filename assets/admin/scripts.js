/* Admin post selector script for Pykam QA
   Uses localized object `pykamQaAdmin` provided by PHP via wp_localize_script
 */
(function($){
	$(document).ready(function() {
		let postTypeFilter = '';

		// REST endpoint for inline autocomplete
		const restUrl = (typeof pykamQaAdmin !== 'undefined' && pykamQaAdmin.rest_url) ? pykamQaAdmin.rest_url : '/wp-json/pykam-qa/v1/posts';
		if (typeof pykamQaAdmin === 'undefined') {
			console && console.warn && console.warn('pykamQaAdmin is not defined. Inline autocomplete may not work.');
		}

		function debounce(fn, wait) {
			let t;
			return function() {
				const args = arguments;
				const ctx = this;
				clearTimeout(t);
				t = setTimeout(function() {
					fn.apply(ctx, args);
				}, wait);
			};
		}

        

		// Remove the attached post
		$(document).on('click', '.remove-post-btn', function() {
			let targetId = $(this).data('target');
			let displayId = $(this).data('display');

			$(targetId).val('');
			$(displayId).val('');

			$('.attached-post-info').remove();
		});

		// Inline autocomplete: listen for input on the inline search field
		$(document).on('input', '.pykam-qa-post-search', debounce(function() {
			let term = $(this).val().trim();
			postTypeFilter = $('#post_type_filter').val() || '';
			let suggestions = $('#pykam_qa_suggestions');

			if (term.length < 2) {
				suggestions.hide().empty();
				return;
			}

			// Build URL (use simple concatenation to avoid URL constructor issues)
			let url = restUrl + '?per_page=10&search=' + encodeURIComponent(term);
			if (postTypeFilter) url += '&post_type=' + encodeURIComponent(postTypeFilter);

			fetch(url, {
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					'X-WP-Nonce': (pykamQaAdmin && pykamQaAdmin.rest_nonce) ? pykamQaAdmin.rest_nonce : ''
				}
			})
			.then(function(response) { return response.json(); })
			.then(function(data) {
				let posts = (data && data.posts) ? data.posts : [];
				let html = '';
				if (posts.length > 0) {
					html += '<ul class="pykam-qa-suggestion-list">';
					posts.forEach(function(post) {
						html += '<li class="pykam-qa-suggestion-item" data-id="' + post.ID + '" data-title="' + escapeHtml(post.post_title) + '">';
						html += '<strong>' + escapeHtml(post.post_title) + '</strong> <span class="suggestion-meta">(' + escapeHtml(post.post_type_label) + ' | ' + post.post_date_formatted + ' | ID:' + post.ID + ')</span>';
						html += '</li>';
					});
					html += '</ul>';
				} else {
					html = '<div class="no-results">' + pykamQaAdmin.i18n.noPostsFound + '</div>';
				}
				suggestions.html(html).show();
			})
			.catch(function() {
				suggestions.html('<div class="error">' + pykamQaAdmin.i18n.loadingError + '</div>').show();
			});
		}, 250));

		// Click handler for suggestion items
		$(document).on('click', '.pykam-qa-suggestion-item', function() {
			let postId = $(this).data('id');
			let postTitle = $(this).data('title');
			$('#pykam_qa_attached_post_id').val(postId);
			$('#attached_post_display').val(postTitle);
			$('#pykam_qa_suggestions').hide().empty();
			// show edit link
			$('.pykam-qa-edit-selected-post').attr('href', '/wp-admin/post.php?post=' + postId + '&action=edit').show();
		});

		function escapeHtml(text) {
			return String(text)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		}

		// Search posts
		$(document).on('click', '#post_search_btn', function() {
			searchQuery = $('#post_search_input').val();
			currentPage = 1;
			loadPosts();
		});

		$(document).on('keypress', '#post_search_input', function(e) {
			if (e.which === 13) {
				searchQuery = $(this).val();
				currentPage = 1;
				loadPosts();
			}
		});

		// Filter by post type
		$(document).on('change', '#post_type_filter', function() {
			postTypeFilter = $(this).val();
			currentPage = 1;
			loadPosts();
		});

		function closeModal() {
			modal.hide();
			overlay.remove();
		}
	});
})(jQuery);
