/**
* Create the controller/service responsible for interacting with the Open Weather Map API
*
* Example data: {"coord":{"lon":139.01,"lat":35.02},"weather":[{"id":800,"main":"Clear","description":"clear sky","icon":"01n"}],"base":"stations","main":{"temp":285.514,"pressure":1013.75,"humidity":100,"temp_min":285.514,"temp_max":285.514,"sea_level":1023.22,"grnd_level":1013.75},"wind":{"speed":5.52,"deg":311},"clouds":{"all":0},"dt":1485792967,"sys":{"message":0.0025,"country":"JP","sunrise":1485726240,"sunset":1485763863},"id":1907296,"name":"Tawarano","cod":200}
*
* @author 	Chris Rogers
* @since 	2017-05-14
*/


import { throwError as observableThrowError, Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient, HttpResponse, HttpHeaders, HttpClientJsonpModule } from '@angular/common/http';
import { AppUrl as App } from '../../app.url';
import { Weather } from './weather';
import { DataStorageService } from '../../app.storage.service';
import { map, catchError } from 'rxjs/operators';

@Injectable()
export class WeatherService {

	uri: string;
	key: string;

	private static readonly API_KEY: string = "5eeb97790d4f6cf008d3613ef077098f";
	private static readonly GOOGLE_API_KEY: string = "AIzaSyDNBoqalASIHil1YXDFpYvMrsGgB--26Yc";

	constructor(private _http: HttpClient, private _jsonp: HttpClientJsonpModule, private storage: DataStorageService) {

		this.uri = "https://api.openweathermap.org/data/2.5/weather?callback=JSONP_CALLBACK&APPID=" + WeatherService.API_KEY + "&units=metric";
		this._http = _http;
		this._jsonp = _jsonp;
	}

	/**
	* Get weather info from open weather map api based on lat and lng
	* @param 	float 	lat
	* @param 	float 	lng
	* @return 	JSON
	*/
	public getWeatherByCoordinates(lat, lng): Observable<any> {

		let u = this.uri + "&lat=" + lat + "&lon=" + lng;
		return this._http.jsonp(u, 'jsonpWeatherByCoordinates').pipe(map(res => res), catchError(this.handleError));
	}

	public jsonpWeatherByCoordinates(data): Weather {

		console.log(data);
		return new Weather(data);
	}

	/**
	* Get timezone info from Google api based on lat and lng
	* @param 	float 	lat
	* @param 	float 	lng
	* @return 	JSON
	*/
	public getTimezoneByCoordinates(lat, lng): Observable<any> {

		let uri = new App().url + 'api/timezone';
		let creds = JSON.stringify({
			lat: lat,
			lng: lng
		});
		let h = new HttpHeaders();
		h.append('Content-Type', 'application/json');
		return this._http.post(uri, creds, { headers: h }).pipe(map(res => res), catchError(this.handleError));
	}

	/**
	* Get weather information from local storage service
	* @return 	any 	weatherData
	*/
	public getWeatherData() {

		let weatherData = JSON.parse(this.storage.getItem('weatherData'));
		weatherData = (typeof weatherData === "string" && weatherData.length > 0) ? JSON.parse(weatherData) : weatherData;
		return weatherData;
	}

	/**
	* Set weather information in local storage service
	* @param 	any 	data
	*/
	public setWeatherData(data: any) {

		data.updated = Math.round((new Date()).getTime() / 1000);
		this.storage.setItem('weatherData', JSON.stringify(data));
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
