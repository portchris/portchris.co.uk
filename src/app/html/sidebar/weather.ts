/**
* The model class for the Weather component
* @author 	Chris Rogers
* @since 	1.0.0 <2017-05-16>
*/

export class Weather {

    public weather: any;

    public constructor(data: any) {

        if (data.hasOwnProperty("weather") && data.hasOwnProperty("name") && data.hasOwnProperty("main")) {
            this.weather = data.weather;
            this.weather.main.temp = Math.floor(this.weather.main.temp);
        }
    }
}