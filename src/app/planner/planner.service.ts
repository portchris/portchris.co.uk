/**
* Create the controller/service responsible for interacting with the Laravel Planner API
*
* @author 	Chris Rogers
* @since 	<1.5.0> 2020-11-26
*/


import { throwError as observableThrowError, Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient, HttpResponse, HttpHeaders } from '@angular/common/http';
import { map, catchError } from 'rxjs/operators';
import { AppUrl as App } from '../app.url';
import { Planner } from './planner';
import { DataStorageService } from '../app.storage.service';

@Injectable()
export class PlannerService {

	public uri: string;
	public token: string;
	public storage: DataStorageService;
	private _http: HttpClient;

	public constructor(_http: HttpClient, storage: DataStorageService) {

		this.uri = new App().url + 'api/sheets';
		this.storage = storage;
		this.token = "";
		this._http = _http;
	}

	/**
	 * Get all the messages from users stream
	 * @return 	Response 	req
	 */
	public getPlanner(): Observable<any> {

		return this._http.get(this.uri).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	 * Set the JWT token ready for to play the game
	 * @param 	string	token
	 */
	public setToken(token: string) {

		this.token = token;
	}

	/**
	 * Get the JWT token ready for to play the game
	 * @return 	string	token
	 */
	public getToken() {

		return this.token;
	}

	/**
	 * Custom error handler
	 * @param 	Response | any 	error
	 */
	private handleError(error: HttpResponse<any> | any) {

		// Might use a remote logging infrastructure for live environment
		let errMsg: string;
		const body = error.body || '';
		const err = body.error || JSON.stringify(body);
		if (error instanceof HttpResponse) {
			errMsg = `${error.status} - ${error.statusText || ''} ${err}`;
		} else {
			errMsg = error.message ? error.message : error.toString();
		}

		if (error.status < 400 || error.status === 500) {

			// This issue is fatal, cause console error
			console.error(errMsg);
			return observableThrowError(errMsg);
		} else {

			// This error is not fatal, const the user know.
			return JSON.parse("[" + err + "]");
		}
	}
}
