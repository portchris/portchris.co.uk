/**
* Create the controller responsible for interacting with the Laravel Messages API
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/

import { Injectable } from '@angular/core';
import { Http, Response } from "@angular/http";
import { Observable } from "rxjs";
import { AppModule } from "../app.module";
import { Messages } from "./messages";

@Injectable()
export class MessagesService {

	constructor(private _http: Http) { 
		
	}

	getMessages():Observable<Messages[]>{

		let App = new AppModule();
		let url = App.url + 'api/message';
		return this._http.get(url).map(res => res.json()).catch(this.handleError)
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
		console.error(errMsg);
		return Observable.throw(errMsg);
	}

}
