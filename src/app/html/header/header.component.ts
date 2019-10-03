/**
* Header component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Header } from './header';

@Component({
	selector: 'app-header',
	templateUrl: './header.html',
	styleUrls: ['./header.component.css'],
})
export class HeaderComponent implements OnInit, OnChanges {

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

	public ngOnInit() {

		this.sub = this.router.data.subscribe((v) => {
			// console.log(v);
		});
	}

	/**
	* When user clicks on navigation, apply classes for styling
	* @param 	object 	event
	*/
	public navigate(event) {
		let target = event.target || event.srcElement || event.currentTarget;
		let navClass = "." + target.className.replace(" ", ".").replace(".active", "");
		event.preventDefault();
		document.querySelector(navClass).classList.remove("active");
		target.classList.add("active");
	}

	public ngOnChanges() {

	}

	public ngOnDestroy() {

		this.sub.unsubscribe();
	}
}
