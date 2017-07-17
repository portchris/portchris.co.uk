/**
* Create the controller/service responsible for interacting with the Laravel User controller
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/

import { Injectable } from '@angular/core';
import { Http, Response, Headers } from "@angular/http";
import { Observable } from "rxjs";
import { AppModule as App } from "../app.module";
import { ImportStory } from "./import.story";
import { DataStorageService } from '../app.storage.service';

@Injectable()
export class ImportStoryService {

	uri: string;

	constructor(private _http: Http, private storage: DataStorageService) { 
		
		this.uri = new App().url + "import";
	}

	/**
	* Import story
	* @param 	string 	id		scene ID
	*/
	public import(id):Observable<ImportStory[]>{

		let h = new Headers();
		h.append('Content-Type', 'application/json');
		return this._http.get(this.uri + "/" + id, { headers: h }).map(res => res.json()).catch(this.handleError);
	}

	private handleError (error: Response | any) {
		
		// Might use a remote logging infrastructure for live environment
		let errMsg: string;
		if (error instanceof Response) {
			const body = error.json() || '';
			const err = body.error || JSON.stringify(body);
			errMsg = `${error.status} - ${error.statusText || ''} ${err}`;
		} else {
			errMsg = error.message ? error.message : error.toString();
		}

		if (error.status < 400 || error.status === 500) {
			console.error(errMsg);
			return Observable.throw(errMsg);
		} else {
			return Observable.throw(errMsg);
		}
	}

}
