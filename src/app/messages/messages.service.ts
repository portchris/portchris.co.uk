/**
* Create the controller/service responsible for interacting with the Laravel Messages API
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/

import { Injectable } from '@angular/core';
import { Http, Response, Headers } from "@angular/http";
import { Observable } from "rxjs";
import { AppModule as App } from "../app.module";
import { Messages } from "./messages";

@Injectable()
export class MessagesService {

	uri: string;
	token: string;

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
		
		console.log(this.token);
		if (this.token) {
			data.token = this.token;
			this.uri += "?token=" + this.token;
		}
		let d = this.serialise(data);
		let h = new Headers();
		h.append('Content-Type', 'application/x-www-form-urlencoded');
		let req = this._http.post(this.uri, d, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Manually create a message without API and return it through observable
	*/
	createMessage(data) {

		return new Promise((resolve, reject) => {
			if (data != null && data.answer != null && data.page != null && data.user != null) {
				let msg = [{
					name: data.answer.name,
					title: data.answer.title,
					key: data.answer.key,
					stage: data.answer.stage,
					type: data.answer.type,
					method: data.answer.method,
					page_id: data.page.id,
					user_id: data.user.id,
					content: data.message
				}];
				resolve(msg);
			} else {
				reject(new Error("Please provide the data in the correct format"));
			}
		});
	}

	/**
	* Try to log the user in. Else if user does not exist, ask to register.
	* @param 	object 	data
	* @return 	JSON 		req
	*/
	authenticate(data) {

		let uri = new App().url + 'api/user/identify';
		let creds = this.serialise(data);
		let h = new Headers();
		h.append('Content-Type', 'application/x-www-form-urlencoded');
		let req = this._http.post(uri, creds, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	createUserAccount(data) {

		let uri = new App().url + 'api/user';
		let creds = this.serialise(data);
		let h = new Headers();
		h.append('Content-Type', 'application/x-www-form-urlencoded');
		let req = this._http.post(uri, creds, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Create guest token based on users name 
	* @param 	JSON 	username
	* @return 	JSON 	req
	*/
	createGuestAccount(username) {

		let uri = new App().url + 'api/user/guest';
		let creds = this.serialise(username);
		let h = new Headers();
		h.append('Content-Type', 'application/x-www-form-urlencoded');
		let req = this._http.post(uri, creds, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Set the JWT token ready for to play the game
	* @param 	string	token
	*/
	setToken(token: string) {
		this.token = token
	}


	/**
	* Custom error handler
	* @param 	Response | any 	error
	*/
	private handleError(error: Response | any) {
		
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
	* Convert JSON object to query string data
	* @param 	JSON 	obj
	* *@return 	string 	str
	*/
	private serialise(obj) {
		var str = [];
		for (var p in obj) {
			if (obj.hasOwnProperty(p)) {
				str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
			}
		}
		return str.join("&");
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
