'use strict';
(function(api, app, $) {

	if (typeof api === typeof undefined) {
		throw 'No api found';
	}

	// ----------------------------
	// scope constants
	// ----------------------------
	const base_url = app.base_url;
	const i18n = app.i18n;
	const selectors = app.selectors;
	const $tbody = $(selectors.root);
	const $load_more = $(selectors.button_load_more);
	const $filters = $(selectors.filters_form);
	const users = {};
	const posts = {};
	const comments = {};

	// save default text
	$load_more.data("default-text", $load_more.text());

	// ----------------------------
	// ui helpers
	// ----------------------------
	const appendProcessRows = list => {
		const $elements = list.map(item => buildRow(item));
		$tbody.append($elements);
	};

	const getFilterArgs = ()=>{
		const filters = $filters.serializeArray();
		const args = {};
		for( let obj of filters){
			args[obj.name] = obj.value;
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
			$load_more.text($load_more.data("default-text"));
			$load_more.removeAttr("disabled");
		} else {
			$load_more.attr("disabled", "disabled");
			$load_more.text(i18n.load_more_done);
		}
	};

	// ----------------------------
	// ui builders
	// ----------------------------

	/**
	 *
	 * @param item
	 * @return {jQuery|HTMLElement}
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
			<td title="Process ID" id="process-${item.process_id}">
				<a class="process-log__process-id more"
				data-pid="${item.id}"
				href="#process-${item.id}"
				>${item.id}</a>
			</td>
			<td>
				${item.created}
			</td>
			<td>${username}</td>
			<td>${item.logs_count} / <small>${item.event_types.join(", ")}</small></td>
			<td>
				<a target="_blank" title="${item.location_url}" href="${item.location_url}">
					${location_url_text}
				</a>
			</td>
		</tr>`;
		return $(row);
	};

	/**
	 *
	 * @param log
	 * @return {jQuery|HTMLElement}
	 */
	const buildLog = (log) => {


		const $item = $(`<li></li>`).addClass('process-log__item');

		const $header = $('<div></div>').addClass('log__header');
		$header.append($('<span></span>').addClass('log__id').text(log.id));
		// $header.append($("<span></span>").addClass("log__type").text(log.event_type));
		$header.append(
			$('<span></span>').addClass('log__message').text(log.message));

		if(log.location_path){
			$header.append(
				$('<span></span>').addClass('log__location-path').text(`in ${log.location_path}`)
			);
		}


		$header.appendTo($item);

		if( log.variables ){
			const $variables = $("<pre/>")
				.addClass("log__variables")
				.text(log.variables)
				.appendTo($item);
		}

		if (log.changed_data_field != null) {

			const $change = $('<div></div>')
				.addClass('log__changed-data')
				.append(
					$(`<span><span class="label">Changed:</span><span class="value">${log.changed_data_field}</span></span>`)
						.addClass('log__changed-data--field'),
				);
			if (log.changed_data_value_old) {
				$change.append(
					$(`<span><span class="label">From:</span><span class="value">${log.changed_data_value_old}</span></span>`)
						.addClass(
							'log__changed-data--value log__changed-data--value-old'),
				);
			}
			if (log.changed_data_value_new) {
				$change.append(
					$(`<span><span class="label">To:</span><span class="value">${log.changed_data_value_new}</span></span>`)
						.addClass(
							'log__changed-data--value log__changed-data--value-new'),
				);
			}
			$('<div></div>')
				.addClass('process-log__first-line')
				.append($change)
				.appendTo($item);
		}

		const $second_line = $('<div></div>')
			.addClass('process-log__second-line');

		$(`<span>Event type: ${log.event_type}</span>`)
			.addClass('log__type')
			.appendTo($second_line);

		if (log.affected_user) {
			const user = users[log.affected_user];
			$(`<span>${i18n.affected_user}: ${user.display_name}</span>`)
				.addClass('log__affected-user')
				.appendTo($second_line);
		}

		if (log.affected_post) {
			const post = posts[log.affected_post];
			$(`<span>${i18n.affected_post}: ${getMaybeLinkedTitle(
				post.post_title, post.edit_link)}</span>`)
				.addClass('log__affected-post')
				.appendTo($second_line);
		}

		if (log.affected_term) {
			$(`<span>${i18n.affected_term}: ${log.affected_term}</span>`)
				.addClass('log__affected-term')
				.appendTo($second_line);
		}
		if (log.affected_comment) {
			const comment = comments[log.affected_comment];

			$(`<span>${i18n.affected_comment}: ${getMaybeLinkedComment(comment.ID, comment.edit_link)}</span>`)
				.addClass('log__affected-comment')
				.appendTo($second_line);
		}

		const now = parseInt(new Date().getTime() / 1000);
		const time_left = getTimeLeft(parseInt(log.expires) - now);
		$('<span></span>')
			.text(`🗑 ${time_left}`)
			.appendTo($second_line)
			.addClass('log__expires')
			.attr('data-expires', log.expires);

		const $info = [];

		for (let key in log) {
			if (!log.hasOwnProperty(key)) {
				continue;
			}
			const value = log[key];
			if (value === null) {
				continue;
			}
			$info.push(buildLogAttribute(key, value));
		}
		const $raw = $('<div></div>')
			.append($info)
			.addClass('process-log__raw');

		return $item.append($second_line).append($raw);
	};

	/**
	 *
	 * @param key
	 * @param value
	 * @return {jQuery|HTMLElement}
	 */
	const buildLogAttribute = (key, value) => {
		return $('<span></span>')
			.text(value)
			.attr(`data-${key}`, value)
			.addClass('process-log__item--attr');
	};
	/**
	 *
	 * @return {jQuery|HTMLElement}
	 */
	const buildLoading = () => {
		return $('<span></span>').addClass('is-loading').text('Loading');
	};

	// ----------------------------
	// Event handlers
	// ----------------------------
	/**
	 * click on a unloaded process row
	 */
	$tbody.on('click', '.process-log__row--process .more', function(e) {
		e.preventDefault();
		const $a = $(this);
		const $toggle = $('<span></span>')
			.addClass('process-log__process-id toggle')
			.text($a.text());
		const process_id = $a.data('pid');
		const $tr = $a.closest('tr');

		$a.parent().append($toggle);
		$a.remove();
		$tr.toggleClass('is-open');

		// add loading
		const $content = $('<td></td>').attr('colspan', 5);
		const $tr_new = $('<tr></tr>')
			.addClass('process-log__row--logs')
			.append($content);
		$tr_new.insertAfter($tr);
		$content.append(buildLoading());

		fetchProcessLogs(process_id)
			.then(json => json.list.map(log => buildLog(log)))
			.then($elements => {
				$content.empty();
				$content.append($('<ul></ul>')
					.addClass('process-log__logs')
					.append($elements));
			});

	});

	/**
	 * click on a already loaded process row
	 */
	$tbody.on('click', '.process-log__row--process .toggle', function(e) {
		$(this).closest('tr').toggleClass('is-open').next().toggle();
	});

	let logsPage = 1;
	$load_more.on('click', function(e){
		e.preventDefault();
		if($load_more.hasClass("is-done")){
			return;
		}
		if($load_more.hasClass("is-loading")) {
			$load_more.text(i18n.load_more_loading_again+" ");
			return;
		}
		$load_more.addClass("is-loading");

		$load_more.text(i18n.load_more_loading);
		const serialized = getFilterSerialized();
		window.history.replaceState(getFilterArgs(), window.document.title, base_url+((serialized.length > 0)? "&"+serialized: "") );
		fetchProcessList(logsPage++, getFilterArgs()).then(json =>{
			appendProcessRows(json.list);
			$load_more.removeClass("is-loading");
			setLoadMoreActive(json.list.length > 0);
		});

	});

	$filters.on('submit', function(e){
		e.preventDefault();
		logsPage = 1;
		$tbody.empty();
		setLoadMoreActive(true);
		$load_more.trigger("click");
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
		return `${(link) ?
			`<a href="${link}" target="_blank">` :
			''}${title}${(link) ? '</a>' : ''}`;
	};
	const getMaybeLinkedComment = (comment_id, link) => {
		return link ? `<a href='${link}'>${comment_id}</a>` : comment_id;
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
	$load_more.trigger("click");

})(ProcessLogAPI, ProcessLogApp, jQuery);
