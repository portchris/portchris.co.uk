/**
* Create the controller/service responsible for interacting with the Laravel Messages API
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/


import {throwError as observableThrowError,  Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { Http, Response, Headers } from "@angular/http";


// import { Observable } from "rxjs";
import { AppModule as App } from "../app.module";
import { Messages } from "./messages";
import { DataStorageService } from '../app.storage.service';

@Injectable()
export class MessagesService {

	uri: string;
	token: string;

	public constructor(private _http: Http, public storage: DataStorageService) { 
		
		this.uri = new App().url + 'api/message';
		this.token = "";
	}

	/**
	* Get all the messages from users stream
	* @return 	Response 	req
	*/
	public getMessages():Observable<Messages[]>{

		let req = this._http.get(this.uri).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* User has submitted an answer, perform store request to Laravel API
	* @param 	object 	data
	* @param 	Response 	req
	*/
	public getResponse(data):Observable<Messages[]>{
		
		let d = JSON.stringify(data);
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.post(this.uri + "?token=" + this.getToken(), d, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Try to log the user in. Else if user does not exist, ask to register.
	* @param 	object 	data
	* @return 	JSON 		req
	*/
	public authenticate(data) {

		let uri = new App().url + 'api/user/identify';
		let creds = JSON.stringify(data);
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.post(uri, creds, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Try to log the user in. Else if user does not exist, ask to register.
	* @param 	object 	data
	* @return 	JSON 		req
	*/
	public login(userId) {

		let uri = new App().url + 'api/user/' + userId + "?token=" + this.getToken();
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.get(uri, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	public createUserAccount(data) {

		let uri = new App().url + 'api/user';
		let creds = JSON.stringify(data);
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.post(uri, creds, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Create guest token based on users name 
	* @param 	JSON 	username
	* @return 	JSON 	req
	*/
	public createGuestAccount(username) {

		let uri = new App().url + 'api/user/guest';
		let creds = JSON.stringify(username);
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.post(uri, creds, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Destroy the JWT token client and server side.
	* @return 	JSON 	req
	*/
	public logOut(data):Observable<Messages[]> {

		let uri = new App().url + 'api/user/logout';
		let d = JSON.stringify(data);
		let h = new Headers();
		this.token = "";
		this.storeUserInfo({
			user: null,
			token: null
		});
		h.append('Content-Type', 'application/json');
		let req = this._http.post(uri, d, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Destroy the JWT token client and server side and set the users progress back to stage 0.
	* @return 	JSON 	req
	*/
	public reset(data):Observable<Messages[]> {

		let uri = new App().url + 'api/user/reset';
		let d = JSON.stringify(data);
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.post(uri, d, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Destroy the JWT token client and server side. Remove the user from our records.
	* @return 	JSON 	req
	*/
	public remove(data):Observable<Messages[]> {

		let uri = new App().url + 'api/user/remove';
		let d = JSON.stringify(data);
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.post(uri, d, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Manually create a message without API and return it through observable
	*/
	public createMessage(data) {

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
	* Use Laravel's hashing formula to hash a password so we limit plain text information passing.
	* @param 	string 	password 	un-hashed password
	* @return 	string 	req 	hashed password JSON 
	*/
	public hashPassword(password) {

		let uri = new App().url + 'api/user/password';
		let d = JSON.stringify({ password: password });
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.post(uri, d, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
	}

	/**
	* Get the portchris user (me)
	* @return 	the admin user information
	*/
	public getAdminUser() {

		let uri = new App().url + 'api/adminuser';
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		let req = this._http.get(uri, { headers: h }).map(res => res.json()).catch(this.handleError);
		return req;
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
	* Use the HTML5 local storage to save information about the user for next time
	* @param 	array 	info
	*/
	public storeUserInfo(info: any) {
		
		this.storage.setItem('currentUser', JSON.stringify({ 
			token: info.token, 
			user: info.user 
		}));
	}

	/**
	* Get information from previous storage
	* @return 	array 	currentUser
	*/
	public getStoredUserInfo() {

		let currentUser = JSON.parse(this.storage.getItem('currentUser'));
		currentUser = (typeof currentUser === "string") ? JSON.parse(currentUser) : currentUser;
		if (currentUser != null && currentUser.token != null) {
			this.setToken(currentUser.token);
		}
		return currentUser;
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
			return observableThrowError(errMsg);
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
