export class Messages {

	public id: any;
	public message: any;
	public response: any;
	public question: any;
	public answer: any;
	public user: any;
	public page: any;
	public csrf: string;
	public title: string;
	public name: string;
	public key: string;
	public stage: string;
	public type: string;
	public method: string;
	public page_id: string;
	public user_id: string;
	public content: string;

	public constructor(data: any) {

		const m = (data instanceof Array) ? data[data.length - 1] : data;
		if (m) {
			console.log(m);
			this.id = m.id ?? 0;
			this.csrf = m.csrf ?? "";
			this.message = m.message ?? "";
			this.response = m.response ?? "";
			this.question = m.question ?? {};
			this.answer = m.answer ?? {};
			this.user = m.user ?? {};
			this.page = m.page ?? {};
			this.name = m.answer.name ?? "";
			this.title = m.answer.title ?? "";
			this.key = m.answer.key ?? "";
			this.stage = m.answer.stage ?? "";
			this.type = m.answer.type ?? "";
			this.method = m.answer.method ?? "";
			this.page_id = m.page.id ?? "";
			this.user_id = m.user.id ?? "";
			this.content = m.message ?? "";
			// this.m.user_id
			// this.m.stage
			// this.m.page_id
			// this.m.id
			// this.m.name
			// this.m.title
			// this.m.type
			// this.m.method
			// this.m.key
			// this.m.csrf
		}

		return this;
	}

	public toJson() {

		return JSON.parse(JSON.stringify(this));
	}
}