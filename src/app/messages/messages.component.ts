import { Component, OnInit } from '@angular/core';
import { MessagesService } from "./messages.service";
import { Messages } from "./messages";

@Component({
	selector: 'app-messages',
	templateUrl: './messages.component.html',
	styleUrls: ['./messages.component.css']
})

export class MessagesComponent implements OnInit {

	messages: Messages[];
	err: any;

	constructor(private messagesService: MessagesService) {

	}

	ngOnInit() {

		this.getMessages();
	}

	getMessages() {
		
		this.messagesService.getMessages().subscribe(
			messages => this.messages = messages,
			error => this.err = <any>error
		)
	}

}
