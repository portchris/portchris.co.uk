/**
* Story importer page component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, ParamMap } from '@angular/router';
import { ImportStory } from "./import.story";
import { ImportStoryService } from "./import.story.service";
import { slideInOutAnimation } from '../animations/slideinout.animation';
import { FormBuilder, Validators } from '@angular/forms';
import { Observable }  from 'rxjs';


@Component({
	selector: 'import.story',
	templateUrl: './import.story.component.html',
	styleUrls: ['./import.story.component.css'],
	animations: [slideInOutAnimation],
	host: { '[@slideInOutAnimation]': '' },
	providers: [ImportStoryService]
})
export class ImportStoryComponent implements OnInit {

	/**
	* General component error message that will appear
	* @var 	any
	*/
	public err: any;

	/**
	* General component success message that will appear
	* @var 	any
	*/
	public success: any;

	/**
	* Route information from parent
	* @var 	ActivatedRoute
	*/
	private router: any;
	
	/**
	* Subscriber to the router 
	* @var 	Observable
	*/
	private sub: any;

	/**
	* Router parameter "id"
	* @var 	string
	*/
	private sceneId: string;

	/**
	* Must pass in the data object for the columns to work. 
	* Accepts columns.class, columns.content
	* @param 	ActivatedRoute  	route
	* @param 	ImportStoryService 	importStoryService
	* @param 	FormBuilder 	formBuilder
	*/
	constructor(private route: ActivatedRoute, private importStoryService: ImportStoryService) { 

		this.router = route;
	}

	/**
	* When view is initialized
	*/
	public ngOnInit() {
	
		this.sub = this.route.params.subscribe(
			(params) => { this.sceneId = params['id']; }
		);
		this.importStoryService.import(this.sceneId).subscribe(
			(info) => { this.success = info },
			(info) => { this.err = info }
		);
	}
}
