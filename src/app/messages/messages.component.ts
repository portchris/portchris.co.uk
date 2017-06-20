import { Component, OnInit, AfterViewChecked, ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { MessagesService } from "./messages.service";
import { Messages } from "./messages";
import { DataStorageService } from '../app.storage.service';
import { slideUpAnimation } from '../animations/slideup.animation';

enum TYPE {
	USER = 0, 
	MESSAGE = 1
}

@Component({
	selector: 'app-messages',
	templateUrl: './messages.component.html',
	styleUrls: ['./messages.component.css'],
	inputs: ['storage'],
	animations: [slideUpAnimation]
})

export class MessagesComponent implements OnInit, AfterViewChecked {

	/**
	* Refernce to a child element
	*/
	@ViewChild('scrollable') private scrollContainer: ElementRef;

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
	* Message type and actions
	* @var 	string
	*/
	messageType: string;
	messageAction: string;
	input_type: string;
	class_selector: string;

	/**
	* These constants are used to limit the message types allowed
	* @const 	object
	*/
	static readonly MSG_ACTIONS: any = [
		{
			id: "user",
			actions: [
				"authenticate",
				"welcome"
			]
		},
		{
			id: "message",
			actions: [
				"talk"
			]
		}
	];

	/**
	* This places the enum above into this exported class.
	* This only maps the iterative position of MSG_ACTIONS 
	* @const 	enum
	*/
	static readonly TYPES = TYPE;

	/**
	* Magic words that create custom functionality
	* @const 	object
	*/
	static readonly MAGIC_WORDS: any = [
		{
			phrase: "Continue as guest",
			method: "continueAsGuest"
		},
		{
			phrase: "Continue as a guest",
			method: "continueAsGuest"
		},
		{
			phrase: "Guest",
			method: "continueAsGuest"
		},
		{
			phrase: "Register",
			method: "registerAccount"
		},
		{
			phrase: "Create an account",
			method: "registerAccount"
		},
		{
			phrase: "Create account",
			method: "registerAccount"
		},
		{
			phrase: "Log out",
			method: "logOut"
		},
		{
			phrase: "Log off",
			method: "logOut"
		},
		{
			phrase: "Start over",
			method: "startOver"
		},
		{
			phrase: "Reset",
			method: "reset"
		}
	];

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
		method: ["", Validators.required],
		input_type: ["text", Validators.required],
		class_selector: ["col-xs-9 offset-xs-3", Validators.required],
		csrf: ["", Validators.required]
	});

	/**
	* The class constructor, used to instantiate necessary variables
	* @param 	object 	MessagesService
	* @param 	object 	FormBuilder
	*/
	public constructor(private messagesService: MessagesService, public formBuilder: FormBuilder) {

		this.messages = [];
		this.user = {
			id: 0,
			stage: "1",
			message: ""
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
		this.input_type = "text";
		this.class_selector = "col-xs-9 offset-xs-3";
	}

	/**
	* The text based adventure logic
	* @param 	object 	event
	* @param 	boolean valid
	*/
	public doRespond(event: any, valid: boolean) {

		if (valid) {
			let data = this.talkForm.value;
			data.user = (this.user != null) ? this.user : {};
			this.decipherMessageType(data);
			this.decipherMessageAction(data);
			this.createUserMessage(data);
			try {
				let magicMethod = this.hasMagicWords(data.content);
				if (!magicMethod) { 
						if (this.messageType === MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id) {

							// User related message
							this.executeMethod(this.messageAction);
						} else {

							// Game related message
							this.messagesService.getResponse(data).subscribe(
								(message) => { this.getMessagesSuccess(message) },
								(error) => { this.getMessagesFail(error) },
								() => { this.getMessagesComplete() }
							);
						}
				} else {
					this.executeMethod(magicMethod);
				}
			} catch(e) {
				console.error(e);
			}
		} else {
			console.error(this.talkForm);
			this.err = this.errMsg;
		}
		this.talkForm.controls['content'].setValue("");
	}

	/**
	* This adds a layer of security around being able to pass method names.
	* I'm trying to avoid eval, and window isn't working like expected so am using switch
	* @param 	method
	*/
	public executeMethod(method) {

		switch(method) {
			case 'toSaveOrNotToSave':
				this.toSaveOrNotToSave();
			break;
			case 'welcome':
				this.welcome();
			break;
			case 'authenticate':
				this.authenticate();
			break;
			case 'getMessages':
				this.getMessages();
			break;
			case 'registerAccount':
				this.registerAccount();
			break;
			case 'continueAsGuest':
				this.continueAsGuest();
			break;
			case 'logOut':
				this.logOut();
			break;
			case 'startOver':
				this.startOver("Okay I'm logging you out and starting over. Don't worry if you have an account, your progress will be safe.");
			break;
			case 'reset':
				this.reset("Starting a new game and logging you off.");
			break;
		}
	}

	/**
	* Angular event. When Angualr is ready and loaded, contruct the basis for our component
	*/
	public ngOnInit() {

		let storage = this.messagesService.getStoredUserInfo();
		if (storage != null && storage.user != null && storage.user.id != null && storage.user.id !== 0 && storage.token.length) {
			this.user = storage.user;
			this.messagesService.login(this.user.id).subscribe(
				(message) => { this.getMessagesSuccess(message); this.messagesService.setToken(message[0].title); },
				(error) => { this.getMessagesFail(error); },
				() => { this.getMessagesComplete(); }
			);
		} else {
			let msg = this.createMessageTemplate();
			msg.message = "Welcome, what is your name?";
			msg.answer.name = "Init";
			msg.answer.title = msg.answer.name;
			msg.answer.key = "question";
			msg.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id;
			msg.answer.method = "welcome";
			msg.user.stage = 0;
			this.messagesService.createMessage(msg)
					.then((message) => { this.getMessagesSuccess(message); })
					.catch((error) => { this.getMessagesFail(error); })
					.then(() => { this.getMessagesComplete(); });
		}
	}

	/**
	* User introduces themselves, the game presents the opportunity to track their progress
	*/
	public welcome() {

		let msg = this.createMessageTemplate();
		let data = this.talkForm.value;
		this.user.name = data.content;
		this.user.firstname = data.content;
		this.user.lastname = data.content;
		if (this.user.name.indexOf(" ") !== -1) {
			let split = this.user.name.split(" ");
			this.user.firstname = split[0];
			split.splice(0, 1);
			this.user.lastname = split.join(" ");
		}
		msg.message = "Welcome " + this.user.name + "! Before we begin, have you previously had saved progress? If not then would you like to save you progress for next time?";
		msg.answer.name = "Init";
		msg.answer.title = msg.answer.name;
		msg.answer.key = "question";
		msg.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id;
		msg.answer.method = "toSaveOrNotToSave";
		this.messageAction = msg.answer.method;
		msg.user.stage = 0;
		this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { this.getMessagesFail(error); })
				.then(() => { this.getMessagesComplete(); });
	}

	/**
	* Attempt to identify the user via session in order to retrieve their previous conversations. 
	* Else require them to login and assign as guest
	*/
	public toSaveOrNotToSave() {

		let msg = this.createMessageTemplate();
		let answer = this.talkForm.value.content.toLowerCase();
		if (answer.indexOf("no") !== -1) {
			this.continueAsGuest();
		} else if (answer.indexOf("yes") !== -1) {
			msg.message = "Excellent choice " + this.user.firstname + ". By tracking your progress you can come back whenever you like and pick up where you left off! In order to track your progress I will require you to create an account or login. Please may I have your email address?";
			msg.answer.name = "Init";
			msg.answer.title = msg.answer.name;
			msg.answer.key = "question";
			msg.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id;
			msg.answer.method = "authenticate";
			msg.user.stage = 0;
			this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { this.getMessagesFail(error); })
				.then(() => { this.getMessagesComplete(); });
			// this.getMessages();
		} else {
			msg.message = "Sorry, did you want to track your progress? Answer 'Yes' or 'No'";
			msg.answer.name = "Init";
			msg.answer.title = msg.answer.name;
			msg.answer.key = "question";
			msg.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id;
			msg.answer.method = "toSaveOrNotToSave";
			msg.user.stage = 0;
			this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { this.getMessagesFail(error); })
				.then(() => { this.getMessagesComplete(); });
		}
	}


	public ngAfterViewChecked() {
		
		this.scrollToBottom();        
	} 

	/**
	* When a new message is loaded, always scroll to the bottom
	*/
	public scrollToBottom(): void {

		try {
			let pos = this.scrollContainer.nativeElement.scrollTop; 
			let dest = this.scrollContainer.nativeElement.scrollHeight;
			console.log("POS", pos);
			console.log("DEST", dest);
			this.scrollContainer.nativeElement.scrollTop = dest;
			// if (pos < dest) {
			// 	var tO = setTimeout(() => {
			// 		pos++;
			// 	}, 100);  
			// } else {
			// 	clearTimeout(tO);   
			// }
		} catch(err) {
			console.error(err);
		}                 
	}

	/**
	* Get the users messages, if user exists and previous conversation exists
	*/
	public getMessages() {
		
		this.messagesService.getMessages().subscribe(
			(message) => { this.getMessagesSuccess(message) },
			(error) => { this.getMessagesFail(error) },
			() => { this.getMessagesComplete() }
		);
	}

	/**
	* Observable / promise success method
	* @param 	string 	message
	*/
	private getMessagesSuccess(message) {
		
		let m = message[message.length - 1];
		if (m) {
			// m.content = this.convertChoiceScriptTemplate(m.content);
			this.messages = this.messages.concat(m);
			this.user.id = m.user_id;
			this.user.stage = m.stage;
			this.page.id = m.page_id;
			this.question.id = m.id;
			this.question.name = m.name;
			this.question.title = m.title;
			this.question.type = m.type;
			this.question.method = m.method;
			this.question.key = m.key;
			this.question.csrf = m.csrf;
			this.class_selector = (m.key === "user") ? "col-xs-9 offset-xs-3" : "offset-xs-3 col-xs-9";
			this.err = "";
			this.talkForm.controls['id_linked_content_meta'].setValue(this.question.id);
			this.talkForm.controls['name'].setValue(this.question.name);
			this.talkForm.controls['title'].setValue(this.question.title);
			this.talkForm.controls['key'].setValue(this.question.key);
			this.talkForm.controls['stage'].setValue(this.user.stage);
			this.talkForm.controls['user_id'].setValue(this.user.id);
			this.talkForm.controls['page_id'].setValue(this.page.id);
			this.talkForm.controls['type'].setValue(this.question.type);
			this.talkForm.controls['method'].setValue(this.question.method);
			this.talkForm.controls['csrf'].setValue(this.question.csrf);
		}
	}

	/**
	* Observable / promise error method
	* @param 	any 	error
	*/
	private getMessagesFail(error: any) {
		
		let errMsg = error;
		console.error(error);
		if (errMsg instanceof Error) {
			errMsg = error.message ? error.message : error.toString();
			errMsg += " File: " + error.fileName + ":" + error.lineNumber;
		}
		this.err = errMsg;
		this.startOver("Error occured: I need to log you out and start over. Don't worry if you have an account, your progress will be saved.");
	}

	/**
	* Observable / promise final method on completion. Will not fire on error.
	* Update the local storage with user and token information
	*/
	private getMessagesComplete() {

		this.user.message = "";
		if (this.user != null && this.messagesService.getToken() != null) {
			this.messagesService.storeUserInfo({
				user: this.user,
				token: this.messagesService.getToken()
			});
		}
	}

	/**
	* Log the user out
	*/
	public logOut() {

		this.messagesService.logOut({}).subscribe(
			(message) => { this.getMessagesSuccess(message); this.ngOnInit(); },
			(error) => { this.getMessagesFail(error) },
			() => { this.getMessagesComplete() }
		);
	}

	/**
	* Something tragic has happened, lets log out with a custom message
	*/
	public startOver(msg: string) {

		let data = {
			message: msg
		};
		this.messagesService.logOut(data).subscribe(
			(message) => { this.getMessagesSuccess(message); this.ngOnInit(); },
			(error) => { 
				let msg = this.createMessageTemplate();
				console.error(error);
				msg.message = "Wow, something really bad happened. I cannot even make a request. Error reads: " + error;
				msg.answer.name = "error";
				msg.answer.title = msg.answer.name;
				msg.answer.key = msg.answer.name;
				msg.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id;
				msg.answer.method = "authenticate";
				msg.user.stage = 0;
				this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { console.error(error) })
				.then(() => { this.getMessagesComplete(); });
			},
			() => { this.getMessagesComplete() }
		);
	}

	/**
	* Start the user over back to stage 1
	*/
	public reset(msg: string) {

		let data = {
			user_id: this.user.id,
			message: msg
		};
		this.messagesService.reset(data).subscribe(
			(message) => {
				this.getMessagesSuccess(message); 
				this.messagesService.logOut({}).subscribe(
					(message) => { this.getMessagesSuccess(message); this.ngOnInit(); },
					(error) => { this.getMessagesFail(error) },
					() => { this.getMessagesComplete() }
				);
			},
			(error) => { this.getMessagesFail(error) },
			() => { this.getMessagesComplete() }
		);
	}

	/**
	* Search users input for magic words that fire custom functionality
	* @param 	string 	content
	* @return boolean|method
	*/
	private hasMagicWords(content) {
		
		let r = false;
		let str1 = content.replace(/\s+/g, '').toLowerCase();
		for (var i = MessagesComponent.MAGIC_WORDS.length - 1; i >= 0; i--) {
			let word = MessagesComponent.MAGIC_WORDS[i];
			let str2 = word.phrase.replace(/\s+/g, '').toLowerCase();
			if (str1 === str2) {
				r = word.method;
				break;	
			}
		}
		return r;
	}

	/**
	* User typed message, add it to messages stream
	* @param 	JSON 	data
	*/
	private createUserMessage(data) {

		if (this.input_type !== "password") {
			let msg = this.createMessageTemplate();
			msg.message = data.content;
			msg.answer.name = "User's input";
			msg.answer.title = this.user.name + " writes message";
			msg.answer.key = "user";
			msg.answer.type = data.type;
			msg.answer.method = data.method;
			msg.page.id = data.page_id;
			msg.user.id = this.user.id;
			msg.user.stage = this.user.stage;
			this.messagesService.createMessage(msg)
					.then((message) => { this.getMessagesSuccess(message); })
					.catch((error) => { this.getMessagesFail(error); })
					.then(() => { this.getMessagesComplete(); });
		}
	}

	/**
	* Default formate when Creating a message
	* @return 	JSON 	message object
	*/
	private createMessageTemplate() {

		let m: any = {
			message: "",
			answer: { 
				name: "",
				title: "",
				key: "",
				type: MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.MESSAGE].id,
				method: MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.MESSAGE].actions[0]
			},
			page: { 
				id: 0
			},
			user: {
				id: this.user.id,
				stage: 1,
			}
		}
		return m;
	}

	/**
	* Since all functionality including login, registration etc is done using the message component
	* we need to decipher what kind of action this is
	* @param 	object 	data
	*/
	private decipherMessageType(data) {

		for (var i = MessagesComponent.MSG_ACTIONS.length - 1; i >= 0; i--) {
			let type = MessagesComponent.MSG_ACTIONS[i];
			if (data.type === type.id) {
				this.messageType = type.id;
				break;
			}
		}

		// Default to message type "message" if type hasn't been established
		if (this.messageType == null) {
			this.messageType = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.MESSAGE].id;
		}
	}

	/**
	* Since all functionality including login, registration etc is done using the message component
	* we need to decipher what kind of action this is
	* @param 	object 	data
	*/
	private decipherMessageAction(data) {
		
		for (var i = MessagesComponent.MSG_ACTIONS.length - 1; i >= 0; i--) {
			let action = MessagesComponent.MSG_ACTIONS[i];
			if (this.messageType === action.id && action.actions.indexOf(data.method) !== -1) {
				this.messageAction = data.method;
				break;
			}
		}

		// Default to message type "message" if type hasn't been established
		if (this.messageAction == null) {
			this.messageAction = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.MESSAGE].actions[0];
		}
	}

	/**
	* Log the user in if credentials are available. Otherwise create message to ask
	* @todo 	Transfer the responses to the API so Laravel can localize strings.
	* @return 	JSON 	message
	*/
	private authenticate() {

		let data = this.talkForm.value;
		let msg = this.createMessageTemplate();
		msg.message = "";
		msg.answer.name = data.name;
		msg.answer.title = data.title;
		msg.answer.key = data.key;
		msg.answer.type = data.type;
		msg.answer.method = data.method;
		msg.page.id = data.page_id
		msg.user.id = data.user_id,
		msg.user.stage = data.stage;
		if (this.user.name == null) {

			// The user is requesting authentication but we don't even know their name! How rude.
			msg.message = "Hold on, I don't even know your name yet. Who are you?";
			msg.answer.method = "welcome";
			this.input_type = "text";
			this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { this.getMessagesFail(error); });
		} else if (this.validateEmail(data.content)) {

			// The user has just entered their email, now we need a password.
			this.user.email = data.content;
			msg.message = "Thanks, and your password please.";
			this.input_type = "password";
			this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { this.getMessagesFail(error); })
				.then(() => { this.getMessagesComplete(); });
		} else if (this.user.email != null) {

			// They have entered their email and password this is enough information to authenticate
			this.messagesService.hashPassword(data.content).subscribe(
				(hash) => { 
					this.user.password = hash.password; 
					this.input_type = "text";
					let credentials = { 
						email: this.user.email, 
						password: data.content
					};
					this.messagesService.authenticate(credentials).subscribe(
						(message) => { 
							this.getMessagesSuccess(message); 
							this.messagesService.setToken(message[0].title); 
							this.user.id = message[0].user_id;
						},
						(error) => { this.getMessagesFail(error); },
						() => { this.getMessagesComplete(); }
					);
				},
				(error) => { this.getMessagesFail(error); }
			);
		} else {

			// The user is being stubborn and isn't providing an email address
			msg.message = 'Sorry, please provide your email address. You can skip this process by typing "guest" and you will continue as a guest.';
			this.input_type = "text";
			this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { this.getMessagesFail(error); });
		}
	}

	/**
	* Massively complex email reqex which I totally made myself...
	* @param 		string 	email
	* @return 	boolean
	*/
	private validateEmail(email) {

		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}

	/**
	* Convert any line breaks in valid HTML break tags
	* @param 	string 	str
	* @param 	boolean isXhtml
	* @see 		https://stackoverflow.com/questions/2919337/jquery-convert-line-breaks-to-br-nl2br-equivalent
	* @return string 
	*/
	private nl2br(str, isXhtml) {

		var breakTag = (isXhtml || typeof isXhtml === 'undefined') ? '<br />' : '<br>';    
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
	}

	/**
	* To import my story I have chosen the popular ChoiceScript templating format which
	* uses ${} for their variables. This will convert that to useful user info.   
	* @param 	string 	str
	*/
	private convertChoiceScriptTemplate(str) {
		
		let re = /\${(.*?)\}/;
		let i = 0;
		do {
			var m = re.exec(str);
			if (m) {
				let info = eval("this.user." + m[1]);
				str = (info) ? str.replace(m[0], info) : str.replace(m[0], "");
				console.log(m);
			}
		} while (m);
		return str;
	}

	/**
	* User has decided to play an unsaved game, continue as guest
	*/
	private continueAsGuest() {

		let data = this.talkForm.value;
		let msg = this.createMessageTemplate();
		let params = { 
			username: (this.user.name != null) ? this.user.name : "Guest", 
			password: "Guest" 
		};
		this.user.id = 0;
		this.user.name = "Guest";
		this.user.firstname = "Joe";
		this.user.lastname = "Bloggs";
		this.user.stage = 1;
		msg.message = "No problem, be my guest. But I can't be blamed for not remembering you next time.";
		msg.answer.name = "Start game";
		msg.answer.title = "Continue as guest";
		msg.answer.key = "answer";
		msg.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.MESSAGE].id;
		msg.answer.method = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.MESSAGE].actions[0];
		msg.page.id = data.page_id;
		msg.user.id = data.user_id;
		msg.user.stage = 1;
		this.messagesService.createGuestAccount(params).subscribe(
			(message) => { this.getMessagesSuccess(message); this.messagesService.setToken(message[0].title); },
			(error) => { this.getMessagesFail(error) },
			() => { this.getMessagesComplete() }
		);
		this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { this.getMessagesFail(error); });
	}

	/**
	* User has decided to create an account and save his/her progress. Fine choice
	*/
	private registerAccount() {

		if (this.user != null && this.user.email != null && this.user.password != null) {
			let data = this.talkForm.value;
			let params = {
				name: this.user.name,
				firstname: this.user.firstname,
				lastname: this.user.lastname,
				email: this.user.email, 
				username: this.user.email, 
				password: this.user.password,
				stage: 1,
				lat: 0.00,
				lng: 0.00
			};
			this.getCurrentLocation(

				// Allowed access to location
				(position) => {
					this.user.lat = position.coords.latitude; 
					this.user.lng = position.coords.longitude;
					params.lat = this.user.lat;
					params.lng = this.user.lng;
					this.messagesService.createUserAccount(params).subscribe(
						(message) => { 
							this.getMessagesSuccess(message); 
							this.messagesService.setToken(message[0].title); 
							this.user.id = message[0].user_id;
						},
						(error) => { this.getMessagesFail(error) },
						() => { this.getMessagesComplete() }
					);
				},

				// Not allowed access to location
				() => {
					this.messagesService.createUserAccount(params).subscribe(
						(message) => { this.getMessagesSuccess(message); this.messagesService.setToken(message[0].title); },
						(error) => { this.getMessagesFail(error) },
						() => { this.getMessagesComplete() }
					);
				}
			);
		} else {
			this.getMessages();
		}
	}

	/**
	* Call getCurrentPosition with success and failure callbacks
	* @param 	callback 	success
	* @param 	callback 	fail
	*/
	private getCurrentLocation(success, fail) {

		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(success, fail);
		}
	}

	/**
	* Set the local storage property for our service
	* @param 	DataStorageService	storage
	*/
	public setStorage(storage: any) {

		this.messagesService.storage = storage;
		console.log(storage);
	}
}
