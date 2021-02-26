import { isDevMode } from '@angular/core';

export class AppUrl { 
	
	url: string;

	constructor() {

		// I'm sorry I had to do it!
		this.url = (isDevMode) ? "http://api.portchris.localhost/" : "https://api.portchris.co.uk/";
	}
}