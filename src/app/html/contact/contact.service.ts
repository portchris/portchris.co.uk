/**
* Create the controller/service responsible for interacting with the Laravel User controller
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/


import { throwError as observableThrowError, Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient, HttpResponse, HttpHeaders } from '@angular/common/http';
import { AppUrl as App } from '../../app.url';
import { Contact } from './contact';
import { DataStorageService } from '../../app.storage.service';
import { map, catchError } from 'rxjs/operators';

@Injectable()
export class ContactService {

	public uri: string;
	public key: string;
	private _http: HttpClient; 
	private storage: DataStorageService;

	constructor(_http: HttpClient, storage: DataStorageService) {

		this.uri = new App().url + "api/enquiry";
		this.storage = storage;
		this._http = _http;
	}

	/**
	* Send enquiry to Wufoo. Requires Wufoo ready data
	* @param 	object 	data
	*/
	public sendEnquiry(data): Observable<any> {

		let d = JSON.stringify(data);
		let h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.post(this.uri, d, { headers: h }).pipe(map(res => res), catchError(this.handleError));
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
