/* Admin post selector script for Pykam QA
   Uses localized object `pykamQaAdmin` provided by PHP via wp_localize_script
 */
(function($){
	$(document).ready(function() {
		let modal = $('#pykam-qa-post-selector');
		let overlay = $('<div class="post-modal-overlay"></div>');
		let currentPage = 1;
		let searchQuery = '';
		let postTypeFilter = '';

		function loadPosts() {
			$.ajax({
				url: pykamQaAdmin.ajax_url,
				type: 'POST',
				data: {
					action: 'pykam_qa_get_posts',
					page: currentPage,
					search: searchQuery,
					post_type: postTypeFilter,
					nonce: pykamQaAdmin.nonce
				},
				beforeSend: function() {
					$('#posts_list_container').html(
						'<div class="loading" style="text-align: center; padding: 20px;">' +
						pykamQaAdmin.i18n.loadingPosts +
						'</div>'
					);
				},
				success: function(response) {
					if (response.success) {
						renderPostsList(response.data);
					} else {
						$('#posts_list_container').html(
							'<div class="error" style="text-align: center; padding: 20px; color: #dc3232;">' +
							(response.data && response.data.message ? response.data.message : pykamQaAdmin.i18n.loadingError) +
							'</div>'
						);
					}
				},
				error: function() {
					$('#posts_list_container').html(
						'<div class="error" style="text-align: center; padding: 20px; color: #dc3232;">' +
						pykamQaAdmin.i18n.loadingError +
						'</div>'
					);
				}
			});
		}

		function renderPostsList(data) {
			let html = '';

			if (data.posts.length > 0) {
				html += '<div class="posts-list">';

				$.each(data.posts, function(index, post) {
					html += '\n                            <div class="post-item" data-id="' + post.ID + '" data-title="' + $('<div>').text(post.post_title).html() + '">\n                                <div>\n                                    <div class="post-title">' + $('<div>').text(post.post_title).html() + '</div>\n                                    <div style="font-size: 12px; color: #666; margin-top: 3px;">\n                                        ID: ' + post.ID + ' | <span class="post-type">' + $('<div>').text(post.post_type_label).html() + '</span> | ' + post.post_date_formatted + '\n                                    </div>\n                                </div>\n                                <div>\n                                    <a href="' + post.edit_link + '" target="_blank" class="button button-small">' + pykamQaAdmin.i18n.view + '</a>\n                                </div>\n                            </div>\n                        ';
				});

				html += '</div>';

				if (data.total_pages > 1) {
					html += '<div class="posts-pagination">';

					if (currentPage > 1) {
						html += '<button class="pagination-btn" data-page="' + (currentPage - 1) + '">' + pykamQaAdmin.i18n.previous + '</button>';
					}

					html += '<span style="padding: 5px 10px;">' + currentPage + ' / ' + data.total_pages + '</span>';

					if (currentPage < data.total_pages) {
						html += '<button class="pagination-btn" data-page="' + (currentPage + 1) + '">' + pykamQaAdmin.i18n.next + '</button>';
					}

					html += '</div>';
				}
			} else {
				html += '<div style="text-align: center; padding: 30px; color: #666;">' + pykamQaAdmin.i18n.noPostsFound + '</div>';
			}

			$('#posts_list_container').html(html);

			// Handlers for selecting a post
			$('.post-item').on('click', function() {
				$('.post-item').removeClass('selected');
				$(this).addClass('selected');

				let postId = $(this).data('id');
				let postTitle = $(this).data('title');
				let targetId = modal.data('target');
				let displayId = modal.data('display');

				$(targetId).val(postId);
				$(displayId).val(postTitle);

				closeModal();

				// Show confirmation message
				alert(pykamQaAdmin.i18n.postSelected);
			});

			// Pagination buttons
			$('.pagination-btn').on('click', function() {
				currentPage = $(this).data('page');
				loadPosts();
			});
		}

		// Open the modal window
		$(document).on('click', '.select-post-btn', function() {
			let targetId = $(this).data('target');
			let displayId = $(this).data('display');

			modal.data('target', targetId);
			modal.data('display', displayId);

			$('body').append(overlay);
			modal.show();
			overlay.show();

			loadPosts();
		});

		// Close the modal window
		$(document).on('click', '.pykam-modal-close', closeModal);
		overlay.on('click', closeModal);

		// Remove the attached post
		$(document).on('click', '.remove-post-btn', function() {
			let targetId = $(this).data('target');
			let displayId = $(this).data('display');

			$(targetId).val('');
			$(displayId).val('');

			$('.attached-post-info').remove();
		});

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
