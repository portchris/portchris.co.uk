/**
* Create the controller/service responsible for interacting with the Laravel Messages API
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/


import { throwError as observableThrowError, Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient, HttpResponse, HttpHeaders } from '@angular/common/http';
import { map, catchError } from 'rxjs/operators';
import { AppUrl as App } from '../app.url';
import { Messages } from './messages';
import { DataStorageService } from '../app.storage.service';

@Injectable()
export class MessagesService {

	public uri: string;
	public token: string;
	public storage: DataStorageService;
	private _http: HttpClient;

	public constructor(_http: HttpClient, storage: DataStorageService) {

		this.uri = new App().url + 'api/message'; // Causes circular dependency
		this.token = "";
		this.storage = storage;
		this._http = _http;
	}

	/**
	* Get all the messages from users stream
	* @return 	Response 	req
	*/
	public getMessages(): Observable<any> {

		let h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.get(this.uri, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* User has submitted an answer, perform store request to Laravel API
	* @param 	object 	data
	* @param 	Response 	req
	*/
	public getResponse(data): Observable<any> {

		const d = JSON.stringify(data);
		const h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.post(this.uri + "?token=" + this.getToken(), d, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Try to log the user in. Else if user does not exist, ask to register.
	* @param 	object 	data
	* @return 	JSON 		req
	*/
	public authenticate(data): Observable<any> {

		const uri = new App().url + 'api/user/identify';
		const creds = JSON.stringify(data);
		const h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.post(uri, creds, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Try to log the user in. Else if user does not exist, ask to register.
	* @param 	object 	data
	* @return 	JSON 		req
	*/
	public login(userId): Observable<any> {

		const uri = new App().url + 'api/user/' + userId + "?token=" + this.getToken();
		const h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.get(uri, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	public createUserAccount(data): Observable<any> {

		const uri = new App().url + 'api/user';
		const creds = JSON.stringify(data);
		const h = new HttpHeaders();
		console.log(creds);
		h.append('Content-Type', 'application/json');
		return this._http.post(uri, creds, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Create guest token based on users name 
	* @param 	JSON 	username
	* @return 	JSON 	req
	*/
	public createGuestAccount(username): Observable<any> {

		const uri = new App().url + 'api/user/guest';
		const creds = JSON.stringify(username);
		const h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.post(uri, creds, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Destroy the JWT token client and server side.
	* @return 	JSON 	req
	*/
	public logOut(data): Observable<any> {

		const uri = new App().url + 'api/user/logout';
		const d = JSON.stringify(data);
		const h = new HttpHeaders();
		this.token = "";
		this.storeUserInfo({
			user: null,
			token: null
		});
		h.append('Content-Type', 'application/json');
		return this._http.post(uri, d, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Destroy the JWT token client and server side and set the users progress back to stage 0.
	* @return 	JSON 	req
	*/
	public reset(data): Observable<any> {

		const uri = new App().url + 'api/user/reset';
		const d = JSON.stringify(data);
		const h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.post(uri, d, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Destroy the JWT token client and server side. Remove the user from our records.
	* @return 	JSON 	req
	*/
	public remove(data): Observable<any> {

		const uri = new App().url + 'api/user/remove';
		const d = JSON.stringify(data);
		const h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.post(uri, d, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Manually create a message without API and return it through observable
	*/
	public createMessage(data): Promise<any> {

		return new Promise((resolve, reject) => {
			if (data != null && data.answer != null && data.page != null && data.user != null) {
				const msg =
					{
						answer: data.answer,
						page: data.page,
						user: data.user,
						name: data.answer.name,
						title: data.answer.title,
						key: data.answer.key,
						stage: data.answer.stage,
						type: data.answer.type,
						method: data.answer.method,
						page_id: data.page.id,
						user_id: data.user.id,
						content: data.message
					}
				;
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
	public hashPassword(password): Observable<any> {

		const uri = new App().url + 'api/user/password';
		const d = JSON.stringify({ password: password });
		const h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.post(uri, d, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Get the portchris user (me)
	* @return 	the admin user information
	*/
	public getAdminUser(): Observable<any> {

		const uri = new App().url + 'api/adminuser';
		const h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.get(uri, { headers: h }).pipe(map((res) => res), catchError(this.handleError));
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

	/**
	* Convert JSON object to query string data
	* @param 	JSON 	obj
	* *@return 	string 	str
	*/
	private serialise(obj) {
		const str = [];
		for (const p in obj) {
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

		const res = [];
		for (const m in obj) {
			if (typeof obj[m] === "function") {
				res.push(m)
			}
		}
		return res;
	}
}
