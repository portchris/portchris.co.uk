import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { MessagesService } from "./messages.service";
import { Messages } from "./messages";

@Component({
	selector: 'app-messages',
	templateUrl: './messages.component.html',
	styleUrls: ['./messages.component.css']
})

export class MessagesComponent implements OnInit {

	/**
	* Message stream
	* @var 	array
	*/
	messages: Messages[];
	
	/**
	* Current question object
	* @var 	object
	*/
	question: any;

	/**
	* The current user object
	* @var 	object
	*/
	user: any;

	/**
	* The current page object
	* @var 	object
	*/
	page: any;

	/**
	* Any errors to report
	* @var 	any
	*/
	err: any;
	errMsg: string;

	/**
	* The form builder validator. Handles the form submission of the text-based adventure
	* @param 	JSON object
	*/
	public talkForm = this.formBuilder.group({
		
		id_linked_content_meta: ["", Validators.required],
		name: ["", Validators.required],
		title: ["", Validators.required],
		key: ["", Validators.required],
		stage: ["", Validators.required],
		user_id: ["", Validators.pattern("[0-9]")],
		page_id: ["", Validators.pattern("[0-9]")],
		content: ["", Validators.compose([Validators.required, Validators.minLength(1)])],
		type: ["", Validators.required],
		method: ["", Validators.required]
	});

	/**
	* The class constructor, used to instantiate necessary variables
	* @param 	object 	MessageService
	* @param 	object 	FormBuilder
	*/
	constructor(private messagesService: MessagesService, public formBuilder: FormBuilder) {

		this.user = {
			id: 0,
			stage: "1",
		};
		this.page = {
			id: 0
		};
		this.question = {
			id: 0,
			id_linked_content_meta: 0,
			name: "",
			title: "",
			key: "question",
			content: "",
			type: "message",
			method: "",
			pattern: ""
		};
	}

	/**
	* The text based adventure logic
	* @param 	object 	event
	* @param 	boolean valid
	*/
	doRespond(event, valid) {

		if (valid) {
			let data = this.talkForm.value;
			// let action = eval("this." + data.method + "();");
			// this.decipherMessageType(data);
			this.messagesService.getResponse(data).subscribe(
				(message) => { this.getMessagesSuccess(message) },
				(error) => { this.getMessagesFail(error) },
				() => { this.getMessageComplete() }
			);
		} else {
			console.error(this.talkForm);
			this.err = this.errMsg;
		}
	}

	/**
	* Angular event. When Angualr is ready and loaded, contruct the basis for our component
	*/
	ngOnInit() {

		this.getMessages();
	}

	/**
	* Attempt to identify the user via session in order to retrieve their previous conversations. 
	* Else require them to login and assign as guest
	*/
	getUser() {

		
	}

	/**
	* Get the users messages, if user exists and previous conversation exists
	*/
	getMessages() {
		
		this.messagesService.getMessages().subscribe(
			(message) => { this.getMessagesSuccess(message) },
			(error) => { this.getMessagesFail(error) },
			() => { this.getMessageComplete() }
		);
	}

	private getMessagesSuccess(message) {
		
		this.messages = message;
		this.user.id = message[0].user_id;
		this.user.stage = message[0].stage;
		this.page.id = message[0].page_id;
		this.question.type = message[0].type;
		this.question.method = message[0].method;
		this.question.key = message[0].key;
		this.err = "";
		// Check if an authentication message came back response came back. If so we need to add some validation
		// if (message.type === "user" && message.key === "authenticate" && message.name.includes('email')) {
		// 	this.errMsg = "Please enter a valid e-mail";
		// 	this.talkForm.controls['content'].validator = ["", Validators.compose([
		// 		Validators.minLength(1),
		// 		Validators.required,
		// 		Validators.pattern('/^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/')
		// 	])];
		// } else {
		// 	this.errMsg = "Your message was invalid";
		// }
	}

	private getMessagesFail(error: any) {
		
		this.err = error;
	}

	private getMessageComplete() {


	}

	/**
	* Since all functionality including login, registration etc is done using the message component
	* we need to decipher what kind of action this 
	*/
	private decipherMessageType(data) {
		
		let r = false;
		console.log(data);
		switch (data.type) {
			case "user":
				if (data.key === "authenticate") {
					if (data.title.includes('email')) {

						// The user just entered their email to login
						let EMAIL_REGEXP = /^[a-z0-9!#$%&'*+\/=?^_`{|}~.-]+@[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*$/i;
						if (data.content.test(EMAIL_REGEXP)) {
							// this.messages[] = {
							// }
						}
					} else if (data.title.includes('password')) {

						// The user just entered their password to login
					}
				} 
			break;
		}
	}

}
