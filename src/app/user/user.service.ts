/**
* Create the controller/service responsible for interacting with the Laravel User controller
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/


import { throwError as observableThrowError, Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { AppModule as App } from '../app.module';
import { User } from './user';
import { map, catchError } from 'rxjs/operators';

@Injectable()
export class UserService {

	uri: string;

	constructor(private _http: Http) {

		this.uri = new App().url + 'api/user';
	}

	identifyUser(): Observable<User[]> {

		return this._http.get(this.uri + '/identify').pipe(map(res => res.json()), catchError(this.handleError));
	}

	private handleError(error: Response | any) {

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
			return observableThrowError(errMsg);
		} else {
			return observableThrowError(errMsg);
		}
	}

}
