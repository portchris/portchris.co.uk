import { Component, OnInit, OnDestroy, ViewChild, ElementRef, Input, HostListener } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { PlannerService } from './planner.service';
import { WeatherService } from '../html/sidebar/weather.service';
import { Planner } from './planner';
import { DataStorageService } from '../app.storage.service';
import { slideUpAnimation } from '../animations/slideup.animation';
import { popInOutAnimation } from '../animations/popinout.animation';

enum TYPE {
	USER = 0,
	MESSAGE = 1
}

enum SCREEN_WIDTH {
	MOBILE = 768,
	TABLET = 1024,
	DESKTOP = 1200
}

@Component({
	selector: 'google-sheets-planner',
	templateUrl: './planner.component.html',
	styleUrls: ['./planner.component.css'],
	// inputs: ['storage'],
	animations: [slideUpAnimation, popInOutAnimation]
	// host: { '(window:keypress)': 'handleKeyboardEvent($event)' }
})

export class PlannerComponent implements OnInit, OnDestroy {

	/**
	 * Refernce to a child element
	 */
	@ViewChild('shadow', { static: false }) private shadowBox: ElementRef;

	/**
	   * Scheduled planner for the week
	   * @var {Object}
	 */
	resourcePlanner: Planner;

	/**
	 * @var {String}
	 */
	today: string;

	/**
	 * @var {Array}
	 */
	days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

	/**
	 * Any errors to report
	 * @var any
	 */
	err: any;
	errMsg: string;

	/**
	 * Any notifications to report
	 * @var any
	 */
	success: any;

	/**
	* The class constructor, used to instantiate necessary variables
	* @param 	{PlannerService} 	plannerService
	*/
	public constructor(
		private plannerService: PlannerService
	) {
		this.plannerService = plannerService;
	}

	/**
	 * Angular event. When Angualr is ready and loaded, contruct the basis for our component
	 */
	public ngOnInit() {

		const d = new Date();
		const planner = this.getPlanner();
		this.today = this.days[d.getDay()];
	}

	/**
	 * Angular event. When view is destroyed
	 */
	public ngOnDestroy() {


	}

	/**
	 * Get this weeks Resrouce Planner from the Google Sheets API for Chris Rogers
	 */
	public getPlanner() {

		this.plannerService.getPlanner().subscribe(
			(plans) => { this.getPlannerSuccess(plans) },
			(error) => { this.getPlannerFail(error) },
			() => { this.getPlannerComplete() }
		);
	}

	/**
	 * Observable / promise success method
	 * @param 	{Object} 	plans
	 */
	private getPlannerSuccess(plans) {

		if (plans.data != null && plans.success) {
			this.resourcePlanner = new Planner();
			for (let i in this.days) {
				if (this.days[i] in plans.data && plans.data[this.days[i]] != null) {
					this.resourcePlanner[this.days[i]] = {
						name: this.days[i],
						active: (this.today === this.days[i]) ? true : false,
						plans: plans.data[this.days[i]]
					};
				}
			}
			this.success = true;
		} else {
			this.getPlannerFail(
				new Error("Invalid response from the resource planner")
			);
		}
	}

	/**
	* Observable / promise error method
	* @param 	any 	error
	*/
	private getPlannerFail(error: any) {

		let errMsg = error;
		console.error(error);
		if (errMsg instanceof Error) {
			errMsg = error.message ? error.message : error.toString();
			errMsg += " File: " + error.fileName + ":" + error.lineNumber;
		}
		this.setError(errMsg);
	}

	/**
	* Observable / promise final method on completion. Will not fire on error.
	* Update the local storage with user and token information
	*/
	private getPlannerComplete() {
	}

	/**
	 * Set the error notification.
	 * @var 	e 	string
	 */
	private setError(e: string) {

		this.err = e;
		if (e.length > 0) {
			setTimeout(() => {
				this.err = "";
			}, 4000);
		}
	}

	/**
	 * Set the success notification.
	 * @var 	s 	string
	 */
	private setSuccess(s: string) {

		this.success = s;
		if (s.length > 0) {
			setTimeout(() => {
				this.success = "";
			}, 4000);
		}
	}
}
