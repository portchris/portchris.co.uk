/**
* Create the controller/service responsible for interacting with the Laravel Messages API
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/

import { Injectable } from '@angular/core';
import { Http, Response } from "@angular/http";
import { Observable } from "rxjs";
import { AppModule as App } from "../app.module";
import { Messages } from "./messages";

@Injectable()
export class MessagesService {

	uri: string;

	constructor(private _http: Http) { 
		
		this.uri = new App().url + 'api/message';
	}

	/**
	* Get all the messages from users stream
	* @return 	Response 	req
	*/
	getMessages():Observable<Messages[]>{

		let req = this._http.get(this.uri).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* User has submitted an answer, perform store request to Laravel API
	* @param 	object 	data
	* @param 	Response 	req
	*/
	getResponse(data):Observable<Messages[]>{
		
		let req = this._http.put(this.uri, data).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Custom error handler
	* @param 	Response | any 	error
	*/
	private handleError (error: Response | any) {
		
		// Might use a remote logging infrastructure for live environment
		let errMsg: string;
		const body = error.json() || '';
		const err = body.error || JSON.stringify(body);
		if (error instanceof Response) {
			errMsg = `${error.status} - ${error.statusText || ''} ${err}`;
		} else {
			errMsg = error.message ? error.message : error.toString();
		}

		if (error.status < 400 || error.status === 500) {
			
			// This issue is fatal, cause console error
			console.error(errMsg);
			return Observable.throw(errMsg);
		} else {

			// This error is not fatal, let the user know.
			return JSON.parse("[" + err + "]");
		}
	}

	/**
	* Cycle an object and return array of methods
	* @param 	object obj
	*/
	private getMethods(obj) {

		var res = [];
		for (var m in obj) {
			if (typeof obj[m] == "function") {
				res.push(m)
			}
		}
		return res;
	}
}
