import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
	static values = {
		enableTime: { type: Boolean, default: false },
		noCalendar: { type: Boolean, default: false },
		dateFormat: { type: String, default: 'Y-m-d' },
		altFormat: { type: String, default: 'Y-m-d' },
		altInput: { type: Boolean, default: true },
		allowInput: { type: Boolean, default: true },
		mode: { type: String, default: 'single' },
		locale: { type: String, default: 'en' },
		minDate: String,
		maxDate: String,
		defaultDate: String,
	};

	async connect() {
		await this.loadTheme();
		await this.initializeFlatpickr();
		this.setupThemeListener();
	}

	async loadTheme() {
		const useDarkMode =
			localStorage.getItem('theme') === 'dark' ||
			(!('theme' in localStorage) &&
				window.matchMedia('(prefers-color-scheme: dark)').matches);
		const theme = useDarkMode ? 'dark' : 'light';
		
        var oppositeTheme = useDarkMode ? 'light' : 'dark';
		document.querySelectorAll(`link[href$="flatpickr_dist_themes_${oppositeTheme}_css.css"]`).forEach(link => {
			link.href = link.href.replace(`${oppositeTheme}`, `${theme}`);
		});
        
        await import(`flatpickr/dist/themes/${theme}.css`);
	}

	async initializeFlatpickr() {
		const options = {
			enableTime: this.enableTimeValue,
			noCalendar: this.noCalendarValue,
			dateFormat: this.dateFormatValue,
			altFormat: this.altFormatValue,
			altInput: this.altInputValue,
			allowInput: this.allowInputValue,
			mode: this.modeValue,
			locale: this.localeValue,
		};

		try {
			const localeModule = await import(
				`flatpickr/dist/l10n/${this.localeValue}.js`
			);
			options.locale = localeModule.default[this.localeValue];
		} catch (e) {
			console.warn(
				`Flatpickr locale '${this.localeValue}' not found, using default`
			);
		}

		if (this.hasMinDateValue) {
			options.minDate = this.minDateValue;
		}
		if (this.hasMaxDateValue) {
			options.maxDate = this.maxDateValue;
		}
		if (this.hasDefaultDateValue) {
			options.defaultDate = this.defaultDateValue;
		}

		this.fp = flatpickr(this.element, options);
	}

	setupThemeListener() {
		document.querySelectorAll('input[name="theme"]').forEach((element) => {
			element.addEventListener('click', async () => {
				setTimeout(async () => {
					if (this.fp) {
						this.fp.destroy();
					}
					await this.loadTheme();
					await this.initializeFlatpickr();
				}, 100);
			});
		});
	}

	disconnect() {
		if (this.fp) {
			this.fp.destroy();
		}
	}
}
