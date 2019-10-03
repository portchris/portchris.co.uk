/**
* 404 component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges, OnDestroy, ViewChild, ElementRef } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { PageNotFound } from './page-not-found';
import { Waves } from './Waves';


@Component({
	selector: 'page-not-found',
	templateUrl: './page-not-found.html',
	styleUrls: ['./page-not-found.component.css']
})
export class PageNotFoundComponent implements OnInit, OnChanges, OnDestroy {

	/**
	* Refernce to a child canvas element
	*/
	@ViewChild('canvas', { static: false }) private canvas: ElementRef;

	router: any;
	sub: any;
	waves: any;

	/**
	* Must pass in the data object for the columns to work. 
	* Accepts columns.class, columns.content
	* @param  ActivatedRoute   route
	*/
	constructor(private route: ActivatedRoute) {

		this.router = route;
	}

	ngOnInit() {

		this.sub = this.router.data.subscribe((v) => {
			console.log(v);
		});
		this.loadCanvas();
	}

	public loadCanvas() {

		this.waves = new Waves(this.canvas.nativeElement, 1200, 700);
		setInterval(() => { this.run() }, 50);
	}

	public run() {

		this.waves.update();
		this.waves.draw();
	}

	ngOnChanges() {

	}

	ngOnDestroy() {

		this.sub.unsubscribe();
	}
}
