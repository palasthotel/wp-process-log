'use strict';
(function(api, app) {

	if (typeof api === typeof undefined) {
		throw 'No api found';
	}

	// ----------------------------
	// scope constants
	// ----------------------------
	const base_url = app.base_url;
	const i18n = app.i18n;
	const selectors = app.selectors;
	const tbody = document.querySelector(selectors.root);
	const load_more = document.querySelector(selectors.button_load_more);
	const filters = document.querySelector(selectors.filters_form);
	const users = {};
	const posts = {};
	const comments = {};

	// save default text
	const load_more_default_text = load_more.textContent;

	// ----------------------------
	// dom helpers
	// ----------------------------

	/**
	 * @param {string} tag
	 * @param {string} [className]
	 * @param {*} [text] set as text, never parsed as markup
	 * @return {HTMLElement}
	 */
	const el = (tag, className, text) => {
		const element = document.createElement(tag);
		if (className) {
			element.className = className;
		}
		if (typeof text !== typeof undefined) {
			element.textContent = text;
		}
		return element;
	};

	/**
	 * Parses markup whose values have already gone through esc(). A <template> parses
	 * table rows as well, which a <div> would drop.
	 * @param {string} markup
	 * @return {Element}
	 */
	const fromHtml = (markup) => {
		const template = document.createElement('template');
		template.innerHTML = markup.trim();
		return template.content.firstElementChild;
	};

	// ----------------------------
	// ui helpers
	// ----------------------------
	const appendProcessRows = list => {
		tbody.append(...list.map(item => buildRow(item)));
	};

	const getFilterArgs = ()=>{
		const args = {};
		for( let [name, value] of new FormData(filters)){
			args[name] = value;
		}
		return args;
	};

	const getFilterSerialized = ()=>{
		const args = getFilterArgs();
		return Object.keys(args).map((key)=>{
			return (args[key] !== "" && args[key].length > 0)? key+"="+args[key]: null;
		}).filter((el)=> el != null).join(("&"));
	};

	const setLoadMoreActive = (isActive)=>{
		if(isActive){
			load_more.textContent = load_more_default_text;
			load_more.disabled = false;
		} else {
			load_more.disabled = true;
			load_more.textContent = i18n.load_more_done;
		}
	};

	// ----------------------------
	// ui builders
	// ----------------------------

	/**
	 * Every logged value can come from a request - the location url is the raw
	 * REQUEST_URI of whoever triggered the log - so nothing goes into markup unescaped.
	 * @param {*} value
	 * @return {string}
	 */
	const esc = (value) => String(value ?? '')
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');

	/**
	 * Edit links arrive in WordPress' display context, already carrying &amp; - undo that
	 * so esc() does not encode it twice.
	 * @param {string} url
	 * @return {string} the url if it is http(s), otherwise an empty string
	 */
	const safeUrl = (url) => (typeof url === 'string' && /^https?:\/\//i.test(url)) ? url.replace(/&amp;/g, '&') : '';

	/**
	 *
	 * @param item
	 * @return {Element}
	 */
	const buildRow = (item) => {
		let username = 'Annonymous';
		if (typeof users[item.active_user] !== typeof undefined) {
			const user = users[item.active_user];
			username = getMaybeLinkedTitle(user.display_name, user.edit_link);
		}
		let location_url_text = item.location_url;
		if (typeof location_url_text === "string" && location_url_text.length > 84) {
			location_url_text = location_url_text.substr(0, 84) + '…';
		}
		const row = `<tr class="process-log__row--process">
			<td title="Process ID" id="process-${esc(item.id)}">
				<a class="process-log__process-id more"
				data-pid="${esc(item.id)}"
				href="#process-${esc(item.id)}"
				>${esc(item.id)}</a>
			</td>
			<td>
				${esc(item.created)}
			</td>
			<td>${username}</td>
			<td>${esc(item.logs_count)} / <small>${esc(item.event_types.join(", "))}</small></td>
			<td>
				<a target="_blank" rel="noopener noreferrer" title="${esc(item.location_url)}" href="${esc(safeUrl(item.location_url))}">
					${esc(location_url_text)}
				</a>
			</td>
		</tr>`;
		return fromHtml(row);
	};

	/**
	 *
	 * @param log
	 * @return {HTMLElement}
	 */
	const buildLog = (log) => {

		const item = el('li', 'process-log__item');

		const header = el('div', 'log__header');
		header.append(el('span', 'log__id', log.id));
		header.append(el('span', 'log__message', log.message));

		if(log.location_path){
			header.append(el('span', 'log__location-path', `in ${log.location_path}`));
		}

		item.append(header);

		if( log.variables ){
			item.append(el('pre', 'log__variables', log.variables));
		}

		if (log.changed_data_field != null) {

			const change = el('div', 'log__changed-data');
			change.append(
				fromHtml(`<span class="log__changed-data--field"><span class="label">Changed:</span><span class="value">${esc(log.changed_data_field)}</span></span>`),
			);
			if (log.changed_data_value_old) {
				change.append(
					fromHtml(`<span class="log__changed-data--value log__changed-data--value-old"><span class="label">From:</span><span class="value">${esc(log.changed_data_value_old)}</span></span>`),
				);
			}
			if (log.changed_data_value_new) {
				change.append(
					fromHtml(`<span class="log__changed-data--value log__changed-data--value-new"><span class="label">To:</span><span class="value">${esc(log.changed_data_value_new)}</span></span>`),
				);
			}
			const first_line = el('div', 'process-log__first-line');
			first_line.append(change);
			item.append(first_line);
		}

		const second_line = el('div', 'process-log__second-line');

		second_line.append(el('span', 'log__type', `Event type: ${log.event_type}`));

		if (log.affected_user) {
			const user = users[log.affected_user];
			second_line.append(
				el('span', 'log__affected-user', `${i18n.affected_user}: ${user.display_name}`),
			);
		}

		if (log.affected_post) {
			const post = posts[log.affected_post];
			second_line.append(
				fromHtml(`<span class="log__affected-post">${esc(i18n.affected_post)}: ${getMaybeLinkedTitle(
					post.post_title, post.edit_link)}</span>`),
			);
		}

		if (log.affected_term) {
			second_line.append(
				el('span', 'log__affected-term', `${i18n.affected_term}: ${log.affected_term}`),
			);
		}
		if (log.affected_comment) {
			const comment = comments[log.affected_comment];
			second_line.append(
				fromHtml(`<span class="log__affected-comment">${esc(i18n.affected_comment)}: ${getMaybeLinkedComment(comment.ID, comment.edit_link)}</span>`),
			);
		}

		const now = parseInt(new Date().getTime() / 1000);
		const time_left = getTimeLeft(parseInt(log.expires) - now);
		const expires = el('span', 'log__expires', `🗑 ${time_left}`);
		expires.setAttribute('data-expires', log.expires);
		second_line.append(expires);

		const raw = el('div', 'process-log__raw');

		for (let key in log) {
			if (!log.hasOwnProperty(key)) {
				continue;
			}
			const value = log[key];
			if (value === null) {
				continue;
			}
			raw.append(buildLogAttribute(key, value));
		}

		item.append(second_line, raw);
		return item;
	};

	/**
	 *
	 * @param key
	 * @param value
	 * @return {HTMLElement}
	 */
	const buildLogAttribute = (key, value) => {
		const attribute = el('span', 'process-log__item--attr', value);
		attribute.setAttribute(`data-${key}`, value);
		return attribute;
	};
	/**
	 *
	 * @return {HTMLElement}
	 */
	const buildLoading = () => {
		return el('span', 'is-loading', 'Loading');
	};

	// ----------------------------
	// Event handlers
	// ----------------------------
	tbody.addEventListener('click', function(e) {
		const more = e.target.closest('.process-log__row--process .more');
		if (more && tbody.contains(more)) {
			e.preventDefault();
			openProcess(more);
			return;
		}
		const toggle = e.target.closest('.process-log__row--process .toggle');
		if (toggle && tbody.contains(toggle)) {
			const tr = toggle.closest('tr');
			tr.classList.toggle('is-open');
			const logs = tr.nextElementSibling;
			if (logs) {
				logs.style.display = (logs.style.display === 'none') ? '' : 'none';
			}
		}
	});

	/**
	 * click on a unloaded process row
	 * @param {HTMLElement} a
	 */
	const openProcess = (a) => {
		const toggle = el('span', 'process-log__process-id toggle', a.textContent);
		const process_id = a.dataset.pid;
		const tr = a.closest('tr');

		a.parentNode.append(toggle);
		a.remove();
		tr.classList.toggle('is-open');

		// add loading
		const content = el('td');
		content.setAttribute('colspan', 5);
		const tr_new = el('tr', 'process-log__row--logs');
		tr_new.append(content);
		tr.after(tr_new);
		content.append(buildLoading());

		fetchProcessLogs(process_id)
			.then(json => json.list.map(log => buildLog(log)))
			.then(elements => {
				const list = el('ul', 'process-log__logs');
				list.append(...elements);
				content.replaceChildren(list);
			});
	};

	let logsPage = 1;
	load_more.addEventListener('click', function(e){
		e.preventDefault();
		if(load_more.classList.contains("is-done")){
			return;
		}
		if(load_more.classList.contains("is-loading")) {
			load_more.textContent = i18n.load_more_loading_again+" ";
			return;
		}
		load_more.classList.add("is-loading");

		load_more.textContent = i18n.load_more_loading;
		const serialized = getFilterSerialized();
		window.history.replaceState(getFilterArgs(), window.document.title, base_url+((serialized.length > 0)? "&"+serialized: "") );
		fetchProcessList(logsPage++, getFilterArgs()).then(json =>{
			appendProcessRows(json.list);
			load_more.classList.remove("is-loading");
			setLoadMoreActive(json.list.length > 0);
		});

	});

	filters.addEventListener('submit', function(e){
		e.preventDefault();
		logsPage = 1;
		tbody.replaceChildren();
		setLoadMoreActive(true);
		load_more.click();
	});

	// ----------------------------
	// pure functions
	// ----------------------------

	/**
	 * time left display
	 * @param {int} s seconds left
	 * @return {string}
	 */
	const getTimeLeft = s => {
		const tmp = [];
		const d = Math.floor(s / (3600 * 24));
		s -= d * 3600 * 24;
		const h = Math.floor(s / 3600);
		s -= h * 3600;
		const m = Math.floor(s / 60);
		s -= m * 60;

		(d) && tmp.push(d + 'd');
		(d || h) && tmp.push(h + 'h');
		(d || h || m) && tmp.push(m + 'm');
		if (h < 2) {
			tmp.push(s + 's');
		}
		return tmp.join(' ');
	};

	const getMaybeLinkedTitle = (title, link) => {
		link = safeUrl(link);
		return `${(link) ?
			`<a href="${esc(link)}" target="_blank">` :
			''}${esc(title)}${(link) ? '</a>' : ''}`;
	};
	const getMaybeLinkedComment = (comment_id, link) => {
		link = safeUrl(link);
		return link ? `<a href="${esc(link)}">${esc(comment_id)}</a>` : esc(comment_id);
	}

	// ----------------------------
	// API calls and processing
	// ----------------------------
	/**
	 * @param page
	 * @param {object} filters
	 * @return {Promise}
	 */
	function fetchProcessList(page = 1, filters = {}) {
		return api.fetchProcessList(page, filters).then(processUsers);
	}

	/**
	 * @param pid
	 * @return {Promise}
	 */
	function fetchProcessLogs(pid) {
		return api.fetchProcessLogs(pid).then(processPosts).then(processComments).then(processUsers);
	}

	/**
	 * @param json
	 * @return {Promise}
	 */
	const processUsers = json => {
		for (let user of Object.values(json.users)) {
			users[user.ID] = user;
		}
		return json;
	};

	/**
	 * @param json
	 * @return {Promise}
	 */
	const processPosts = json => {
		for (let post of Object.values(json.posts)) {
			posts[post.ID] = post;
		}
		return json;
	};

	/**
	 * @param json
	 * @return {Promise}
	 */
	const processComments = json => {
		for (let comment of Object.values(json.comments)) {
			comments[comment.ID] = comment;
		}
		return json;
	};

	// ----------------------------
	// init application
	// ----------------------------
	load_more.click();

})(ProcessLogAPI, ProcessLogApp);
