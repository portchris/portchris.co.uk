import { Component, OnInit } from '@angular/core';
import { UserService } from './user.service';
import { User } from './user';
// import { Messages } from '../messages/messages';

@Component({
	selector: 'app-user',
	templateUrl: './user.component.html',
	styleUrls: ['./user.component.css']
})
export class UserComponent implements OnInit {

	users: User[];
	// messages: Messages[];
	errMesg: any;

	constructor(private userService: UserService) { }

	ngOnInit() {

		this.getUser();
	}

	getUser() {

		console.log(this.userService.identifyUser().subscribe(
			users => this.users = users,
			error => this.errMesg = <any>error
		));
	}

}
