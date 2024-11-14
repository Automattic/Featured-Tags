import apiFetch from '@wordpress/api-fetch';

import './admin.scss';

// Attach click handlers to all featured tag indicators.
document.querySelectorAll('tbody .column-featured_tag a').forEach(tag => {
	tag.addEventListener('click', event => {
		event.preventDefault();

		const termId = tag.getAttribute('data-term-id');
		const featured = tag.getAttribute('data-featured') === 'yes';

		apiFetch({
			path: 'featured-tags/v1/featured-tag/' + termId,
			method: 'POST',
		})
			.then(response => {
				if (response) {
					tag.setAttribute('data-featured', featured ? 'no' : 'yes');
					tag.textContent = featured ? 'no' : 'yes';
				}
			})
			.catch(error => {
				console.error(error);
			});
	});
});
