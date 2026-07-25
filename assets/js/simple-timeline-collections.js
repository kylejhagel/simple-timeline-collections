/*! Simple Timeline Collections front-end script. Version 1.9.4 */
(function () {
	'use strict';

	var MOBILE_QUERY = '(max-width: 640px)';
	var ENHANCED_CLASS = 'is-stc-enhanced';
	var scheduled = false;

	function getGap(wrap) {
		var raw = wrap.getAttribute('data-stc-alternate-card-gap') || '';
		var gap = parseInt(raw, 10);

		if (Number.isNaN(gap)) {
			gap = 100;
		}

		return Math.max(0, Math.min(300, gap));
	}

	function getMarkerSize(item) {
		var beforeStyle = window.getComputedStyle(item, '::before');
		var markerSize = parseFloat(beforeStyle.width || beforeStyle.inlineSize || '16');

		return Number.isNaN(markerSize) || markerSize <= 0 ? 16 : markerSize;
	}

	function getMarkerOffset(item) {
		var beforeStyle = window.getComputedStyle(item, '::before');
		var offset = parseFloat(
			beforeStyle.getPropertyValue('inset-block-start') ||
			beforeStyle.top ||
			'16'
		);

		return Number.isNaN(offset) || offset < 0 ? 16 : offset;
	}

	function parsePosition(value, referenceSize) {
		if (!value || 'auto' === value) {
			return NaN;
		}

		if (String(value).indexOf('%') > -1) {
			var percentage = parseFloat(value);

			return Number.isNaN(percentage) ? NaN : referenceSize * (percentage / 100);
		}

		var pixels = parseFloat(value);

		return Number.isNaN(pixels) ? NaN : pixels;
	}

	function resetRail(wrap) {
		var timeline = wrap.querySelector('.stc-timeline');

		wrap.classList.remove(ENHANCED_CLASS);

		if (timeline) {
			timeline.style.removeProperty('--stc-parent-rail-start');
			timeline.style.removeProperty('--stc-parent-rail-end');
			timeline.style.removeProperty('--stc-parent-rail-x');
		}
	}

	function resetItems(items) {
		items.forEach(function (item) {
			item.style.marginBlockStart = '';
		});
	}

	function compactAlternateWrap(wrap, items) {
		if (window.matchMedia && window.matchMedia(MOBILE_QUERY).matches) {
			return false;
		}

		var gap = getGap(wrap);
		var previousCardsBySide = {
			left: null,
			right: null
		};

		items.forEach(function (item) {
			var card = item.querySelector('.stc-timeline__card');
			var side = item.getAttribute('data-stc-timeline-side');

			if (!card || ('left' !== side && 'right' !== side)) {
				return;
			}

			var previousCard = previousCardsBySide[side];

			if (previousCard) {
				var previousBottom = previousCard.getBoundingClientRect().bottom;
				var currentTop = card.getBoundingClientRect().top;
				var currentGap = currentTop - previousBottom;
				var offset = currentGap - gap;

				if (offset > 0) {
					item.style.marginBlockStart = '-' + Math.round(offset) + 'px';
				}
			}

			previousCardsBySide[side] = card;
		});

		return true;
	}

	function getMarkerCenterYRelativeToTimeline(timeline, item) {
		var timelineRect = timeline.getBoundingClientRect();
		var itemRect = item.getBoundingClientRect();
		var markerOffset = getMarkerOffset(item);
		var markerSize = getMarkerSize(item);

		return (itemRect.top - timelineRect.top) + markerOffset + (markerSize / 2);
	}

	function getMarkerCenterXRelativeToTimeline(timeline, item) {
		var timelineRect = timeline.getBoundingClientRect();
		var itemRect = item.getBoundingClientRect();
		var beforeStyle = window.getComputedStyle(item, '::before');
		var inlineStart = parsePosition(
			beforeStyle.getPropertyValue('inset-inline-start') ||
			beforeStyle.left,
			itemRect.width
		);

		if (Number.isNaN(inlineStart)) {
			inlineStart = itemRect.width / 2;
		}

		return (itemRect.left - timelineRect.left) + inlineStart;
	}

	function updateParentRail(wrap, items) {
		var timeline = wrap.querySelector('.stc-timeline');

		if (!timeline || items.length < 2) {
			return;
		}

		if (window.matchMedia && window.matchMedia(MOBILE_QUERY).matches) {
			return;
		}

		var firstMarkerCenterY = getMarkerCenterYRelativeToTimeline(timeline, items[0]);
		var lastMarkerCenterY = getMarkerCenterYRelativeToTimeline(timeline, items[items.length - 1]);
		var railCenterX = getMarkerCenterXRelativeToTimeline(timeline, items[0]);

		if (lastMarkerCenterY <= firstMarkerCenterY) {
			return;
		}

		timeline.style.setProperty('--stc-parent-rail-start', Math.round(firstMarkerCenterY) + 'px');
		timeline.style.setProperty('--stc-parent-rail-end', Math.round(lastMarkerCenterY) + 'px');
		timeline.style.setProperty('--stc-parent-rail-x', Math.round(railCenterX) + 'px');
		wrap.classList.add(ENHANCED_CLASS);
	}

	function updateWrap(wrap) {
		var items = Array.prototype.slice.call(wrap.querySelectorAll('.stc-timeline__item'));

		if (!items.length) {
			resetRail(wrap);
			return;
		}

		resetRail(wrap);
		resetItems(items);

		// Markers intentionally use the same CSS-defined vertical offset for every event.
		if (wrap.classList.contains('stc-timeline-wrap--layout-alternate') && wrap.classList.contains('stc-timeline-wrap--compact-alternate')) {
			if (compactAlternateWrap(wrap, items)) {
				updateParentRail(wrap, items);
			}
		}
	}

	function closeEventActions(dropdown) {
		var toggle = dropdown.querySelector('.stc-timeline__actions-toggle');
		var menu = dropdown.querySelector('.stc-timeline__actions-menu');

		if (!toggle || !menu) {
			return;
		}

		toggle.setAttribute('aria-expanded', 'false');
		menu.hidden = true;
		dropdown.classList.remove('is-open');
	}

	function closeAllEventActions(exceptDropdown) {
		document.querySelectorAll('[data-stc-event-actions]').forEach(function (dropdown) {
			if (dropdown !== exceptDropdown) {
				closeEventActions(dropdown);
			}
		});
	}

	function openEventActions(dropdown) {
		var toggle = dropdown.querySelector('.stc-timeline__actions-toggle');
		var menu = dropdown.querySelector('.stc-timeline__actions-menu');

		if (!toggle || !menu) {
			return;
		}

		closeAllEventActions(dropdown);
		toggle.setAttribute('aria-expanded', 'true');
		menu.hidden = false;
		dropdown.classList.add('is-open');
	}

	function toggleEventActions(dropdown) {
		var toggle = dropdown.querySelector('.stc-timeline__actions-toggle');

		if (!toggle) {
			return;
		}

		if ('true' === toggle.getAttribute('aria-expanded')) {
			closeEventActions(dropdown);
		} else {
			openEventActions(dropdown);
		}
	}

	function getMenuItems(menu) {
		return Array.prototype.slice.call(menu.querySelectorAll('[role="menuitem"]'));
	}

	function focusMenuItem(menu, direction) {
		var items = getMenuItems(menu);

		if (!items.length) {
			return;
		}

		var activeIndex = items.indexOf(document.activeElement);
		var nextIndex = 0;

		if ('last' === direction) {
			nextIndex = items.length - 1;
		} else if ('next' === direction) {
			nextIndex = activeIndex >= 0 ? (activeIndex + 1) % items.length : 0;
		} else if ('previous' === direction) {
			nextIndex = activeIndex >= 0 ? (activeIndex - 1 + items.length) % items.length : items.length - 1;
		}

		items[nextIndex].focus();
	}

	function initEventActionMenus() {
		document.addEventListener('click', function (event) {
			var toggle = event.target.closest('.stc-timeline__actions-toggle');
			var confirmLink = event.target.closest('[data-stc-confirm]');

			if (confirmLink) {
				if (!window.confirm(confirmLink.getAttribute('data-stc-confirm'))) {
					event.preventDefault();
					return;
				}
			}

			if (toggle) {
				var dropdown = toggle.closest('[data-stc-event-actions]');

				if (dropdown) {
					event.preventDefault();
					toggleEventActions(dropdown);
				}

				return;
			}

			if (!event.target.closest('[data-stc-event-actions]')) {
				closeAllEventActions();
			}
		});

		document.addEventListener('keydown', function (event) {
			var toggle = event.target.closest('.stc-timeline__actions-toggle');
			var menu = event.target.closest('.stc-timeline__actions-menu');

			if (toggle) {
				var dropdown = toggle.closest('[data-stc-event-actions]');

				if (!dropdown) {
					return;
				}

				if ('Escape' === event.key) {
					closeEventActions(dropdown);
					toggle.focus();
				}

				if ('ArrowDown' === event.key || 'ArrowUp' === event.key || 'Enter' === event.key || ' ' === event.key) {
					event.preventDefault();
					openEventActions(dropdown);
					focusMenuItem(dropdown.querySelector('.stc-timeline__actions-menu'), 'ArrowUp' === event.key ? 'last' : 'first');
				}

				return;
			}

			if (menu) {
				var parentDropdown = menu.closest('[data-stc-event-actions]');
				var parentToggle = parentDropdown ? parentDropdown.querySelector('.stc-timeline__actions-toggle') : null;

				if ('Escape' === event.key) {
					event.preventDefault();
					if (parentDropdown) {
						closeEventActions(parentDropdown);
					}
					if (parentToggle) {
						parentToggle.focus();
					}
				}

				if ('ArrowDown' === event.key) {
					event.preventDefault();
					focusMenuItem(menu, 'next');
				}

				if ('ArrowUp' === event.key) {
					event.preventDefault();
					focusMenuItem(menu, 'previous');
				}
			}
		});
	}

	function updateTimelines() {
		document.querySelectorAll('.stc-timeline-wrap').forEach(updateWrap);
	}

	function scheduleUpdate() {
		if (scheduled) {
			return;
		}

		scheduled = true;

		window.requestAnimationFrame(function () {
			scheduled = false;
			updateTimelines();
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		initEventActionMenus();
		updateTimelines();

		if ('fonts' in document && document.fonts && document.fonts.ready) {
			document.fonts.ready.then(scheduleUpdate).catch(function () {});
		}

		if ('ResizeObserver' in window) {
			var observer = new ResizeObserver(scheduleUpdate);
			document.querySelectorAll('.stc-timeline-wrap .stc-timeline__card').forEach(function (card) {
				observer.observe(card);
			});
		}
	});

	window.addEventListener('load', scheduleUpdate);
	window.addEventListener('resize', scheduleUpdate);
}());
