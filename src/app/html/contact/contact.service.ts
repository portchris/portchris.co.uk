/**
* Create the controller/service responsible for interacting with the Laravel User controller
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/


import {throwError as observableThrowError,  Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { Http, Response, Headers } from "@angular/http";
import { AppModule as App } from "../../app.module";
import { Contact } from "./contact";
import { DataStorageService } from '../../app.storage.service';

@Injectable()
export class ContactService {

	uri: string;
	key:string;

	constructor(private _http: Http, private storage: DataStorageService) { 
		
		this.uri = new App().url + "api/enquiry";
	}

	/**
	* Send enquiry to Wufoo. Requires Wufoo ready data
	* @param 	object 	data
	*/
	public sendEnquiry(data):Observable<Contact[]>{

		let d = JSON.stringify(data);
		let h = new Headers();
		h.append('Content-Type', 'application/json');
		return this._http.post(this.uri, d, { headers: h }).map(res => res.json()).catch(this.handleError);
	}

	/**
	* Get contact form information from local storage service
	* @return 	any 	contactData
	*/
	public getContactFormData() {

		let contactData = JSON.parse(this.storage.getItem('contactData'));
		return contactData;
	}

	/**
	* Set contact form information in local storage service
	* @param 	any 	data
	*/
	public setContactFormData(data: any) {

		this.storage.setItem('contactData', JSON.stringify(data));
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
			return observableThrowError(errMsg);
		} else {
			return observableThrowError(errMsg);
		}
	}

}
