'use strict';
;(function(api, app, $){

	if(typeof api === typeof undefined) throw "No api found";

	const selectors = app.selectors;
	const $tbody = $(selectors.root);

	const buildRow = (item)=>{
		const row = `<tr class="process-log__row">
			<td id="process-${item.process_id}">
				<a class="more" data-pid="${item.process_id}" href="#process-${item.process_id}">
				+ ${item.created}
				</a>
			</td>
			<td>Active User</td>
			<td>${item.logs_count}</td>
			<td>${item.location_url}</td>
		</tr>`;
		return $(row);
	};

	const buildLog = (log) => {
		const item = `<li>
			${log.id}
		</li>`;
		return $(item);
	};

	api.fetchProcessList().then(json => {
		const $elements = json.list.map(item => buildRow(item));
		$tbody.append($elements);
	});

	$tbody.on("click", ".process-log__row .more", function(e){
		e.preventDefault();
		const $a = $(this);
		const process_id = $a.data("pid");
		const $tr = $a.closest("tr");
		$a.wrapInner("<span>").children().unwrap();

		api.fetchProcessLogs(process_id).then(json =>{
			console.log(json);
			return json.list.map((log)=> buildLog(log));
		}).then($elements =>{

			const $tr_new = $("<tr></tr>").append("td").attr("rowspan", 4);
			$tr_new.append($("<ul></ul>").append($elements))
			$tbody.prepend($tr_new);
			// $tr.insertAfter($tr_new);
			// $tr_new.append($("<ul></ul>").append($elements));
		});

	});


})(ProcessLogAPI, ProcessLogApp, jQuery);