/**
* The model class for the contact page component
* @author 	Chris Rogers
* @since 	1.0.0 <2017-05-16>
*/

export class Contact {

	public name: string;
	public email: string;
	public message: string;
	public leaveBlank: string;

	public constructor(data: any) {

		this.name = data.name;
		this.email = data.email;
		this.message = data.message;
		this.leaveBlank = data.leaveBlank;
	}
}