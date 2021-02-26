export class User {

	public id: any;
	public name: string;
	public firstname: string;
	public lastname: string;
	public email: string;
	public username: string;
	public password: string;
	public lat: string;
	public lng: string;
	public stage: string;

	public constructor(data: any) {

		this.id = data.id;
		this.name = data.name;
		this.firstname = data.firstname;
		this.lastname = data.lastname;
		this.email = data.email;
		this.username = data.username;
		this.password = data.password;
		this.lat = data.lat;
		this.lng = data.lng;
		this.stage = data.stage;
	}
}