/**
* Create the controller/service responsible for interacting with the Laravel User controller
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/


import { throwError as observableThrowError, Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient, HttpResponse, HttpHeaders } from '@angular/common/http';
import { AppUrl as App } from '../app.url';
import { ImportStory } from './import.story';
import { DataStorageService } from '../app.storage.service';
import { map, catchError } from 'rxjs/operators';

@Injectable()
export class ImportStoryService {

	public uri: string;
	private _http: HttpClient; 
	private storage: DataStorageService;

	constructor(_http: HttpClient, storage: DataStorageService) {

		this.uri = new App().url + "import";
		this.storage = storage;
		this._http = _http;
	}

	/**
	* Import story
	* @param 	string 	id		scene ID
	*/
	public import(id: string): Observable<any> {

		let h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.get(this.uri + "/" + id, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	private handleError(error: HttpResponse<any> | any) {

		// Might use a remote logging infrastructure for live environment
		let errMsg: string;
		if (error instanceof HttpResponse) {
			const body = error.body || '';
			const err = body.error || JSON.stringify(body);
			errMsg = `${error.status} - ${error.statusText || ''} ${err}`;
		} else {
			errMsg = error.message ? error.message : error.toString();
		}

		if (error.status < 400 || error.status === 500) {
			console.error(errMsg);
			return observableThrowError(errMsg);
		} else {
			return observableThrowError(errMsg);
		}
	}

}
