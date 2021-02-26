/**
* Create the controller/service responsible for interacting with the Laravel User controller
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/


import { throwError as observableThrowError, Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient, HttpResponse } from '@angular/common/http';
import { AppUrl as App } from '../app.url';
import { User } from './user';
import { map, catchError } from 'rxjs/operators';

@Injectable()
export class UserService {

	public uri: string;
	private _http: HttpClient;

	constructor(_http: HttpClient) {

		this.uri = new App().url + 'api/user';
		this._http = _http;
	}

	identifyUser(): Observable<User[]> {

		return this._http.get(this.uri + '/identify').pipe(map(res => [new User(res)]), catchError(this.handleError));
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
