import { Component, OnInit, OnDestroy, AfterViewChecked, ViewChild, ElementRef, Input, HostListener } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { MessagesService } from './messages.service';
import { WeatherService } from '../html/sidebar/weather.service';
import { Messages } from './messages';
import { DataStorageService } from '../app.storage.service';
import { slideUpAnimation } from '../animations/slideup.animation';
import { popInOutAnimation } from '../animations/popinout.animation';

enum TYPE {
	USER = 0,
	MESSAGE = 1
}

enum SCREEN_WIDTH {
	MOBILE = 768,
	TABLET = 1024,
	DESKTOP = 1200
}

@Component({
	selector: 'app-messages',
	templateUrl: './messages.component.html',
	styleUrls: ['./messages.component.css'],
	// inputs: ['storage'],
	animations: [slideUpAnimation, popInOutAnimation]
	// host: { '(window:keypress)': 'handleKeyboardEvent($event)' }
})

export class MessagesComponent implements OnInit, AfterViewChecked, OnDestroy {

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
	* This places the enum above into this exported class.
	* @const 	enum
	*/
	static readonly SCREEN_WIDTHS = SCREEN_WIDTH;

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
		},
		{
			phrase: "Forget about me",
			method: "remove"
		},
		{
			phrase: "Remove me from the guestbook",
			method: "remove"
		}
	];

	/**
	* Refernce to a child element
	*/
	@ViewChild('scrollable', { static: false }) private scrollContainer: ElementRef;
	@ViewChild('searchFauxInput', { static: false }) private searchFauxInput: ElementRef;
	@ViewChild('searchBox', { static: false }) private searchBox: ElementRef;
	@ViewChild('shadow', { static: false }) private shadowBox: ElementRef;

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
	* Any notifications to report
	* @var 	any
	*/
	success: any;

	/**
	* Is user scrolling
	* @var 	boolean
	*/
	userScrolling: boolean;

	/**
	* Timeout variable
	* @var 	any
	*/
	timer: any;

	/**
	* Message type and actions
	* @var 	string
	*/
	messageType: string;
	messageAction: string;
	input_type: string;
	class_selector: string;
	keyboardActions: any;

	/**
	* Display the typing bubble when computer is writing a response
	* @var 	boolean
	*/
	typing: boolean;

	/**
	 * Is current device a touch device
	 * @var boolean
	 */
	touchDevice: boolean;

	/**
	 * Device screen width
	 * @var any
	 */
	deviceWidth: any;

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
	public constructor(
		private messagesService: MessagesService,
		public formBuilder: FormBuilder,
		private weatherService: WeatherService
	) {
		const width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
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
		this.typing = false;
		this.userScrolling = false;
		this.touchDevice = false;
		this.setDeviceWidth(width);
		this.talkForm.disable();
		this.keyboardActions = [];
	}

	/**
	* Method called whenever user emits keydown event.
	* @param 	KeyboardEvent	event
	*/
	// @HostListener('document:keypress', ['$event'])
	@HostListener('window:keypress', ['$event'])
	handleKeyboardEvent(event: KeyboardEvent) {

		// let key = event.key;
		// this.keyboardActions.push(key);
		// if (this.keyboardActions.length > 5) {
		// 	this.keyboardActions.pop();
		// }
		this.userScrolling = false;
	}

	@HostListener('ontouchstart')
	onTouchStart() {
		this.touchDevice = true;
	}

	@HostListener('window:resize', ['$event'])
	onResize(event) {
		this.debounce(() => {
			this.setDeviceWidth(event.target.innerWidth);
			console.log("Device width: " + this.deviceWidth);
		}, 1000);
	}

	/**
	 * @param int width 
	 */
	private setDeviceWidth(width) {
		this.deviceWidth = width;
	}

	/**
	* The text based adventure logic
	* @param 	object 	event
	* @param 	boolean valid
	*/
	public doRespond(event: any, valid: boolean) {

		if (valid) {
			const data = this.talkForm.value;
			data.user = (this.user != null) ? this.user : {};
			data.content = (this.sanitiseContent(data.content));
			this.talkForm.disable();
			this.decipherMessageType(data);
			this.decipherMessageAction(data);
			this.createUserMessage(data);
			try {
				const magicMethod = this.hasMagicWords(data.content);
				if (!magicMethod) {
					if (this.messageType === MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id) {

						// User related message
						this.executeMethod(this.messageAction);
					} else {

						// Game related message
						this.userScrolling = false;
						this.scrollToBottom();
						this.messagesService.getResponse(data).subscribe(
							(message) => { this.getMessagesSuccess(message) },
							(error) => { this.getMessagesFail(error) },
							() => { this.getMessagesComplete() }
						);
					}
				} else {
					this.executeMethod(magicMethod);
				}
			} catch (e) {
				console.error(e);
			}
		} else {
			console.error(this.talkForm);
			this.setError(this.errMsg);
		}
		this.talkForm.controls['content'].setValue("");
	}

	/**
	* Limit length of users message
	* Strip HTML
	* @param 	string 	users content
	* @return 	string 	users sanitised content
	*/
	private sanitiseContent(c) {

		const tmp = document.createElement("DIV");
		tmp.innerHTML = c.substring(0, 200);
		return tmp.textContent || tmp.innerText || "";
	}

	/**
	* This adds a layer of security around being able to pass method names.
	* I'm trying to avoid eval, and window isn't working like expected so am using switch
	* @param 	method
	*/
	public executeMethod(method) {

		switch (method) {
			case 'toSaveOrNotToSave':
				this.toSaveOrNotToSave();
				break;
			case 'welcome':
				this.welcome();
				break;
			case 'teaCoffeeOrWater':
				this.teaCoffeeOrWater();
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
				this.startOver("Okay I'm logging you out and starting over. Don't worry if you've signed the guestbook, your progress will be safe.");
				break;
			case 'reset':
				this.reset("Okay I'm logging you out for the day and resetting your progress back to stage 0.");
				break;
			case 'remove':
				this.remove("Okay I'm removing you from the guestbook and any future records.");
				break;
		}
	}

	/**
	* Angular event. When Angualr is ready and loaded, contruct the basis for our component
	*/
	public ngOnInit() {

		this.typing = true;
		this.talkForm.disable();
		const storage = this.messagesService.getStoredUserInfo();
		if (storage != null && storage.user != null && storage.user.id != null && storage.user.id !== 0 && storage.token.length) {
			this.user = storage.user;
			this.messagesService.login(this.user.id).subscribe(
				(message) => { this.getMessagesSuccess(message); this.messagesService.setToken(message[0].title); },
				(error) => { this.getMessagesFail(error); },
				() => { this.getMessagesComplete(); }
			);
		} else {
			const msg = this.createMessageTemplate();
			const weather = this.weatherService.getWeatherData();
			if (typeof weather !== "undefined" && weather !== null && weather.time != null) {
				msg.message = (weather.time.dark) ? "Hello! " : "Hello! ";
			} else {
				msg.message = "Hello!";
			}
			msg.message += "You must be the new intern. Welcome to the PortChris office, my name is Lucy. I am the receptionist for Chris Rogers - our manager. What is your name?";
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

		// Add focus to input to guide users eyes
		// this.searchBox.nativeElement.classList.add("js");
		// this.searchFauxInput.nativeElement.classList.add("js");
		this.reEnableTalkInput();
		if (this.scrollContainer) {
			this.scrollContainer.nativeElement.addEventListener('scroll', this.isScrolling, true);
		}
	}

	public ngOnDestroy() {

		this.scrollContainer.nativeElement.removeEventListener('scroll', this.isScrolling, true);
	}

	/**
	* User introduces themselves, the game presents the opportunity to track their progress
	*/
	public welcome() {

		const msg = this.createMessageTemplate();
		const data = this.talkForm.value;
		this.talkForm.disable();
		this.user.name = this.sanitiseContent(data.content);
		this.user.firstname = this.sanitiseContent(data.content);
		this.user.lastname = this.sanitiseContent(data.content);
		if (this.user.name.indexOf(" ") !== -1) {
			const split = this.user.name.split(" ");
			this.user.firstname = split[0];
			split.splice(0, 1);
			this.user.lastname = split.join(" ");
		}
		msg.message = "Welcome " + this.user.name + "! Can we get you a drink? Tea, coffee or even a water?";
		msg.answer.name = "Init";
		msg.answer.title = msg.answer.name;
		msg.answer.key = "question";
		msg.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id;
		msg.answer.method = "teaCoffeeOrWater";
		this.messageAction = msg.answer.method;
		msg.user.stage = 0;
		this.messagesService.createMessage(msg)
			.then((message) => { this.getMessagesSuccess(message); })
			.catch((error) => { this.getMessagesFail(error); })
			.then(() => { this.getMessagesComplete(); });
	}

	/**
	* Bit of a gimmick, fires a notification
	*/
	public teaCoffeeOrWater() {

		const msg = this.createMessageTemplate();
		const drink = this.talkForm.value.content.toLowerCase();
		if (drink.indexOf("tea") !== -1 || drink.indexOf("coffee") !== -1 || drink.indexOf("water") !== -1) {
			msg.message = "Enjoy, " + this.user.name + ". Whilst you enjoy your " + drink + ", would you like to sign the vistors guestbook so we remember you next time?";
			msg.answer.method = "toSaveOrNotToSave";
			setTimeout(() => {
				this.setSuccess("You acquired a " + drink);
			}, 2000);
		} else {
			msg.message = "Sorry, I didn't catch that. Did you want a 'tea', 'coffee' or 'water'?";
			msg.answer.method = "teaCoffeeOrWater";
		}
		msg.answer.name = "Init";
		msg.answer.title = msg.answer.name;
		msg.answer.key = "question";
		msg.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id;
		msg.user.stage = 0;
		this.messageAction = msg.answer.method;
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

		const msg = this.createMessageTemplate();
		const answer = this.talkForm.value.content.toLowerCase();
		this.talkForm.disable();
		if (answer.indexOf("no") !== -1) {
			this.continueAsGuest();
		} else if (answer.indexOf("yes") !== -1) {
			msg.message = "Excellent choice " + this.user.firstname + ". Please can you write your email address so I can remember you for next time. Don't worry about anything happening to your details, our guestbook is locked up so nobody outside this office is allowed to see.";
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
			msg.message = "Sorry, did you want to sign the guestbook? Answer 'Yes' or 'No'";
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

		if (!this.userScrolling || this.typing) {
			this.scrollToBottom();
		}
	}

	// @HostListener('scroll')
	public isScrolling = (): void => {

		this.userScrolling = true;
		if (this.timer !== null) {
			clearTimeout(this.timer);
		}
		this.timer = setTimeout(() => {
			this.userScrolling = true; // Keep it true for now
		}, 3000);
	}

	/**
	* When a new message is loaded, always scroll to the bottom
	*/
	public scrollToBottom(): void {

		try {
			const pos = this.scrollContainer.nativeElement.scrollTop;
			const dest = this.scrollContainer.nativeElement.scrollHeight;
			this.scrollContainer.nativeElement.scrollTop = dest;
			// if (pos < dest) {
			// 	var tO = setTimeout(() => {
			// 		pos++;
			// 	}, 100);  
			// } else {
			// 	clearTimeout(tO);   
			// }
		} catch (err) {
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
	* @param 	boolean	delay
	*/
	private getMessagesSuccess(message, delay = true) {

		const m = message[message.length - 1];
		if (m) {
			this.typing = (m.key === "user") ? false : true;
			this.talkForm.disable();
			const fnc = () => {
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
				this.setError("");
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
				this.typing = false;
				this.userScrolling = false;
				this.scrollToBottom();
				this.reEnableTalkInput();
			};
			if (m.key === "user" || !delay) {
				fnc();
			} else {
				setTimeout(fnc, 2000);
			}
		}
	}

	private reEnableTalkInput() {
		setTimeout(() => {
			this.talkForm.enable();
			this.unfocusInput();
			if (this.deviceWidth >= MessagesComponent.SCREEN_WIDTHS.TABLET && !this.touchDevice) {
				this.focusInput();
			}
		}, 2000);
	}

	public unfocusInput() {
		if (this.searchBox) {
			this.searchBox.nativeElement.blur();
		}
	}

	public focusInput() {
		if (this.searchBox) {
			setTimeout(this.searchBox.nativeElement.focus(), 2000);
		}
	}

	/**
	* Observable / promise error method
	* @param 	any 	error
	*/
	private getMessagesFail(error: any) {

		this.reEnableTalkInput();
		let errMsg = error;
		console.error(error);
		if (errMsg instanceof Error) {
			errMsg = error.message ? error.message : error.toString();
			errMsg += " File: " + error.fileName + ":" + error.lineNumber;
		}
		this.setError(errMsg);
		this.startOver("Error occured: I need to log you out and start over. Don't worry if you have an account, your progress will be saved.");
	}

	/**
	* Observable / promise final method on completion. Will not fire on error.
	* Update the local storage with user and token information
	*/
	private getMessagesComplete() {

		this.user.message = "";
		if (this.user != null && this.user.id !== 0 && this.messagesService.getToken().length > 0) {
			this.messagesService.storeUserInfo({
				user: this.user,
				token: this.messagesService.getToken()
			});
		}
		this.checkMessagesOverflowed();
	}

	/**
	* Set the error notification.
	* @var 	e 	string
	*/
	private setError(e: string) {

		this.err = e;
		if (e.length > 0) {
			setTimeout(() => {
				this.err = "";
			}, 4000);
		}
	}

	/**
	* Set the success notification.
	* @var 	s 	string
	*/
	private setSuccess(s: string) {

		this.success = s;
		if (s.length > 0) {
			setTimeout(() => {
				this.success = "";
			}, 4000);
		}
	}


	/**
	* If the message stream overflows the content height then apply a shadow to indicate to the user 
	* this area is scrollable
	*/
	private checkMessagesOverflowed() {

		if (this.scrollContainer.nativeElement.scrollHeight > this.scrollContainer.nativeElement.clientHeight) {
			this.shadowBox.nativeElement.classList.remove("hidden");
		} else {
			this.shadowBox.nativeElement.classList.add("hidden");
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

		const data = {
			message: msg
		};
		this.messagesService.logOut(data).subscribe(
			(message) => { this.getMessagesSuccess(message); this.ngOnInit(); },
			(error) => {
				const m = this.createMessageTemplate();
				console.error(error);
				m.message = "Wow, something really bad happened. I cannot even make a request. Error reads: " + error;
				m.answer.name = "error";
				m.answer.title = m.answer.name;
				m.answer.key = m.answer.name;
				m.answer.type = MessagesComponent.MSG_ACTIONS[MessagesComponent.TYPES.USER].id;
				m.answer.method = "authenticate";
				m.user.stage = 0;
				this.messagesService.createMessage(m)
					.then((message) => { this.getMessagesSuccess(message); })
					.catch((err) => { console.error(err) })
					.then(() => { this.getMessagesComplete(); });
			},
			() => { this.getMessagesComplete() }
		);
	}

	/**
	* Start the user over back to stage 1
	* @var 	string 	msg
	*/
	public reset(msg: string) {

		const data = {
			user_id: this.user.id,
			message: msg
		};
		this.messagesService.reset(data).subscribe(
			(message) => {
				this.getMessagesSuccess(message);
				this.messagesService.logOut({}).subscribe(
					(m) => { this.getMessagesSuccess(m); this.ngOnInit(); },
					(error) => { this.getMessagesFail(error) },
					() => { this.getMessagesComplete() }
				);
			},
			(error) => { this.getMessagesFail(error) },
			() => { this.getMessagesComplete() }
		);
	}

	/**
	* Remove the users record
	* @var 	string 	msg
	*/
	public remove(msg: string) {

		const data = {
			user_id: this.user.id,
			message: msg
		};
		this.messagesService.remove(data).subscribe(
			(message) => {
				this.getMessagesSuccess(message);
				this.messagesService.logOut({}).subscribe(
					(m) => { this.getMessagesSuccess(m); this.ngOnInit(); },
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
		const str1 = content.replace(/\s+/g, '').toLowerCase();
		for (let i = MessagesComponent.MAGIC_WORDS.length - 1; i >= 0; i--) {
			const word = MessagesComponent.MAGIC_WORDS[i];
			const str2 = word.phrase.replace(/\s+/g, '').toLowerCase();
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
			const msg = this.createMessageTemplate();
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

		const m: any = {
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

		for (let i = MessagesComponent.MSG_ACTIONS.length - 1; i >= 0; i--) {
			const type = MessagesComponent.MSG_ACTIONS[i];
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

		for (let i = MessagesComponent.MSG_ACTIONS.length - 1; i >= 0; i--) {
			const action = MessagesComponent.MSG_ACTIONS[i];
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

		const data = this.talkForm.value;
		const msg = this.createMessageTemplate();
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
			msg.message = "Thanks, and a password please.";
			this.input_type = "password";
			this.messagesService.createMessage(msg)
				.then((message) => { this.getMessagesSuccess(message); })
				.catch((error) => { this.getMessagesFail(error); })
				.then(() => { this.getMessagesComplete(); });
		} else if (this.user.email != null) {

			// They have entered their email and password this is enough information to authenticate
			const pass = data.content.substring(0, 255);
			this.messagesService.hashPassword(pass).subscribe(
				(hash) => {
					this.user.password = hash.password;
					this.input_type = "text";
					const credentials = {
						email: this.user.email,
						password: pass
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

		const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
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

		const breakTag = (isXhtml || typeof isXhtml === 'undefined') ? '<br />' : '<br>';
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
	}

	/**
	* User has decided to play an unsaved game, continue as guest
	*/
	private continueAsGuest() {

		const data = this.talkForm.value;
		const msg = this.createMessageTemplate();
		const params = {
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
		this.messagesService.createMessage(msg)
			.then((message) => { this.getMessagesSuccess(message, false); this.typing = true; this.userScrolling = false; })
			.catch((error) => { this.getMessagesFail(error); });
		this.messagesService.createGuestAccount(params).subscribe(
			(message) => { this.getMessagesSuccess(message); this.messagesService.setToken(message[0].title); },
			(error) => { this.getMessagesFail(error) },
			() => { this.getMessagesComplete() }
		);
	}

	/**
	 * Returns a function, that, as long as it continues to be invoked, will not
	 * be triggered. The function will be called after it stops being called for
	 * N milliseconds. If `immediate` is passed, trigger the function on the
	 * leading edge, instead of the trailing.
	 * @param func 
	 * @param wait 
	 * @param immediate 
	 */
	private debounce(func, wait, immediate = false) {
		let timeout;
		const context = this, args = arguments;
		const later = () => {
			timeout = null;
			if (!immediate) {
				func.apply(context, args);
			}
		};
		const callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) {
			func.apply(context, args);
		}
	};

	/**
	* User has decided to create an account and save his/her progress. Fine choice
	*/
	private registerAccount() {

		if (this.user != null && this.user.email != null && this.user.password != null) {
			const data = this.talkForm.value;
			const params = {
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

		console.log("Where are you user?");
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
	}
}
