/*! Simple Timeline Collections admin script. Version 1.9.4 */
(function () {
	'use strict';

	function normalizeHex(value) {
		if (!value) {
			return '';
		}

		var trimmed = value.trim();
		return /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(trimmed) ? trimmed : '';
	}

	function updatePreviewGroup(group) {
		var beforeInput = group.querySelector('[data-stc-color-input="before"]');
		var currentInput = group.querySelector('[data-stc-color-input="current"]');
		var cardInput = group.querySelector('[data-stc-color-input="card"]');
		var railInput = group.querySelector('[data-stc-color-input="rail"]');

		if (!beforeInput || !currentInput || !cardInput || !railInput) {
			return;
		}

		var beforeColor = normalizeHex(beforeInput.value) || beforeInput.getAttribute('data-stc-default-color') || '#2d7b83';
		var currentColor = normalizeHex(currentInput.value) || normalizeHex(beforeInput.value) || currentInput.getAttribute('data-stc-default-color') || beforeColor;
		var cardColor = normalizeHex(cardInput.value) || cardInput.getAttribute('data-stc-default-color') || '#ffffff';
		var railColor = normalizeHex(railInput.value) || railInput.getAttribute('data-stc-default-color') || '#b5c2ce';

		group.querySelectorAll('[data-stc-color-swatch="before"]').forEach(function (swatch) {
			swatch.style.backgroundColor = beforeColor;
		});
		group.querySelectorAll('[data-stc-color-value="before"]').forEach(function (el) {
			el.textContent = beforeColor;
		});

		group.querySelectorAll('[data-stc-color-swatch="current"]').forEach(function (swatch) {
			swatch.style.backgroundColor = currentColor;
		});
		group.querySelectorAll('[data-stc-color-value="current"]').forEach(function (el) {
			el.textContent = currentColor;
		});

		group.querySelectorAll('[data-stc-color-swatch="card"]').forEach(function (swatch) {
			swatch.style.backgroundColor = cardColor;
		});
		group.querySelectorAll('[data-stc-color-value="card"]').forEach(function (el) {
			el.textContent = cardColor;
		});

		group.querySelectorAll('[data-stc-color-swatch="rail"]').forEach(function (swatch) {
			swatch.style.backgroundColor = railColor;
		});
		group.querySelectorAll('[data-stc-color-value="rail"]').forEach(function (el) {
			el.textContent = railColor;
		});

		var map = group.querySelector('[data-stc-selector-map]');
		if (map) {
			map.style.setProperty('--stc-preview-before', beforeColor);
			map.style.setProperty('--stc-preview-current', currentColor);
			map.style.setProperty('--stc-preview-card-bg', cardColor);
			map.style.setProperty('--stc-preview-rail', railColor);
		}

		group.querySelectorAll('[data-stc-selector-card-preview]').forEach(function (card) {
			card.style.backgroundColor = cardColor;
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-stc-color-preview-group]').forEach(function (group) {
			updatePreviewGroup(group);
			group.querySelectorAll('[data-stc-color-input]').forEach(function (input) {
				['input', 'change'].forEach(function (eventName) {
					input.addEventListener(eventName, function () {
						updatePreviewGroup(group);
					});
				});
			});
		});
	});
}());
