/**
* Portfolio / about component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Portfolio } from "./portfolio";
import { slideInOutAnimation } from '../../animations/slideinout.animation';

@Component({
	selector: 'portfolio',
	templateUrl: './portfolio.html',
	animations: [slideInOutAnimation],
	host: { '[@slideInOutAnimation]': '' }
})
export class PortfolioComponent implements OnInit, OnChanges, OnDestroy {

	router: any;
	sub: any;

	/**
	* Must pass in the data object for the columns to work. 
	* Accepts columns.class, columns.content
	* @param  ActivatedRoute   route
	*/
	public constructor(private route: ActivatedRoute) { 

		this.router = route;
	}

	/**
	* When view is initialised
	*/
	public ngOnInit() {
	
		this.sub = this.router.data.subscribe((v) => { this.subscriber(v) });	
	}
	
	/**
	* When view updates
	*/
	public ngOnChanges() {

	}

	/**
	* When view is destroyed
	*/
	public ngOnDestroy() {

		this.sub.unsubscribe();
	}

	/**
	* Router information observable
	* @param 	Observable 	v
	*/
	subscriber(v) {

	}
}
