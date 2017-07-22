/**
* HTML 5 storage service for passing data between routes.
* @author 	Chris Rogers
* @since 	2017-06-18
*/
import { Injectable } from "@angular/core";

@Injectable()
export class DataStorageService {
	
	public storage: any;

	constructor() {

		this.allStorage();
	}

	/**
	* Use the HTML5 local storage to save information to call between routes / page loads
	* @param 	array 	value
	*/
	public setItem(key: string, value: any) {
		
		this.storage[key] = value;
		localStorage.setItem(key, JSON.stringify(value));
	}

	/**
	* Get information from previous storage
	* @return 	any 	currentUser
	*/
	public getItem(key: string) {

		let item = (this.storage[key] != null) ? this.storage[key] : JSON.parse(localStorage.getItem(key));
		return item;
	}

	/**
	* Check if storage is empty
	* @return 	boolean
	*/
	public get isEmpty(): boolean {

		return this.storage === null || this.storage === undefined;
	}

	/**
	* Get all the localStorage information into our property
	*/
	private allStorage() {

		this.storage = (this.storage == null) ? [] : this.storage;
		for (var i = 0; i < localStorage.length; i++) {
			this.storage[localStorage.key(i)] = localStorage.getItem(localStorage.key(i));
		}
	}
}