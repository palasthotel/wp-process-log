'use strict';
;(function(api, app, $){

	if(typeof api === typeof undefined) throw "No api found";

	const selectors = app.selectors;
	const $tbody = $(selectors.root);
	const users = {};

	const buildRow = (item)=>{

		let username = "Annonymous";
		console.log(users);
		if( typeof users[item.active_user] !== typeof undefined){
			username = users[item.active_user].display_name;
		}

		const row = `<tr class="process-log__row--process">
			<td id="process-${item.process_id}">
				<a class="more" data-pid="${item.process_id}" href="#process-${item.process_id}">
				+ ${item.created}
				</a>
			</td>
			<td>${username}</td>
			<td>${item.logs_count}</td>
			<td>${item.location_url}</td>
		</tr>`;
		return $(row);
	};
	const ignore_attrs = ["created", "process_id", "active_user", "location_url"];
	const buildLog = (log) => {
		const $infos = [];

		for(let key in log){
			if(ignore_attrs.includes(key)) continue;
			const value = log[key];
			if(value === null) continue;

			$infos.push(buildLogAttribute(key, value));
		}

		const $item = $(`<li></li>`).append($infos);
		return $item.addClass('process-log__item');
	};
	const buildLogAttribute = (key, value)=>{
		return $("<span></span>")
		.text(value)
		.attr(`data-${key}`, value)
		.addClass("process-log__item--attr");
	};

	api.fetchProcessList()
	.then(json =>{
		console.log(json);
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
		const $content = $("<td></td>").attr("colspan", 4);
		const $tr_new = $("<tr></tr>").addClass('process-log__row--logs').append($content);
		$tr_new.insertAfter($tr);
		$content.text("Loading...");

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
	})


})(ProcessLogAPI, ProcessLogApp, jQuery);