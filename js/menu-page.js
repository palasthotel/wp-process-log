'use strict';
;(function(api, app, $){

	if(typeof api === typeof undefined) throw "No api found";

	const selectors = app.selectors;
	const $tbody = $(selectors.root);
	const users = {};

	const buildRow = (item)=>{

		let username = "Annonymous";
		if( typeof users[item.active_user] !== typeof undefined){
			const user = users[item.active_user];
			username = `<a target="_blank" href="${user.edit_link}">${user.display_name}</a>`
		}
		let location_url_text = item.location_url;
		if(location_url_text.length > 84){
			location_url_text = location_url_text.substr(0, 84)+"…";
		}
		const row = `<tr class="process-log__row--process">
			<td title="Process ID" id="process-${item.process_id}">
				<a class="more" data-pid="${item.process_id}" href="#process-${item.process_id}">
				+${item.process_id}
				</a>
			</td>
			<td>
				${item.created}
			</td>
			<td>${username}</td>
			<td>${item.logs_count}</td>
			<td>
				<a target="_blank" title="${item.location_url}" href="${item.location_url}">
					${location_url_text}
				</a>
			</td>
		</tr>`;
		return $(row);
	};
	const ignore_attrs = ["created", "process_id", "active_user", "location_url", "expires"];
	const buildLog = (log) => {
		const $info = [];

		for(let key in log){
			if(!log.hasOwnProperty(key)) continue;
			if(ignore_attrs.includes(key)) continue;
			const value = log[key];
			if(value === null) continue;

			$info.push(buildLogAttribute(key, value));
		}

		const $item = $(`<li></li>`).addClass('process-log__item');

		const $first_line = $("<div></div>").append($info);
		const $second_line = $("<div></div>").addClass("process-log__second-line");
		const now = parseInt(new Date().getTime() / 1000);

		const time_left = getTimeLeft( parseInt(log.expires) - now );

		$("<span></span>").text(`⏱ ${time_left}`).appendTo($second_line);

		return $item
			.append($first_line)
			.append($second_line);
	};
	const buildLogAttribute = (key, value)=>{
		return $("<span></span>")
		.text(value)
		.attr(`data-${key}`, value)
		.addClass("process-log__item--attr");
	};
	const buildLoading = ()=>{
		return $("<span></span>").addClass("is-loading").text("Loading")
	};

	api.fetchProcessList()
	.then(json =>{
		for(let user of Object.values(json.users)){
			users[user.ID] = user;
		}
		return json;
	})
	.then(json => {
		const $elements = json.list.map(item => buildRow(item));
		$tbody.append($elements);
	});

	$tbody.on("click", ".process-log__row--process .more", function(e){
		e.preventDefault();
		const $a = $(this);
		const $toggle = $("<span></span>").addClass("toggle").text($a.text());
		const process_id = $a.data("pid");
		const $tr = $a.closest("tr");

		$a.parent().append($toggle);
		$a.remove();
		$tr.toggleClass("is-open");

		// add loading
		const $content = $("<td></td>").attr("colspan", 5);
		const $tr_new = $("<tr></tr>").addClass('process-log__row--logs').append($content);
		$tr_new.insertAfter($tr);
		$content.append(buildLoading());

		api.fetchProcessLogs(process_id)
		.then(json =>{
			return json.list.map((log)=> buildLog(log));
		}).then($elements =>{
			$content.empty();
			$content.append($("<ul></ul>").addClass("process-log__logs").append($elements));
		});

	});

	$tbody.on("click", ".process-log__row--process .toggle", function(e){
		$(this).closest("tr").toggleClass("is-open").next().toggle();
	});

	/**
	 * time left display
	 * @param {int} s seconds left
	 * @return {string}
	 */
	const getTimeLeft = s => {
		const tmp = [];
		const d = Math.floor(s / (3600 * 24));
		s  -= d * 3600 * 24;
		const h   = Math.floor(s / 3600);
		s  -= h * 3600;
		const m = Math.floor(s / 60);
		s  -= m * 60;

		(d) && tmp.push(d + 'd');
		(d || h) && tmp.push(h + 'h');
		(d || h || m) && tmp.push(m + 'm');
		if(h < 2) tmp.push(s + 's');
		return tmp.join(' ');
	}


})(ProcessLogAPI, ProcessLogApp, jQuery);