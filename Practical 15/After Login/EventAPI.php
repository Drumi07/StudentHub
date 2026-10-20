<?php
require_once __DIR__ . '/auth_check.php';
requireRole('admin');
if (empty($_SESSION['csrf_api_token'])) $_SESSION['csrf_api_token'] = bin2hex(random_bytes(32));
$csrf = htmlspecialchars($_SESSION['csrf_api_token'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Hub - Fetch API Event Management</title>
<link rel="stylesheet" href="style.css">
<style>
.api-practical13{max-width:1100px;margin:35px auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 8px 28px rgba(15,23,42,.08)}
.api-practical13 h1{margin-top:0}.api-practical13 .api-form{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;margin:20px 0}
.api-practical13 input,.api-practical13 select{min-width:0;padding:11px;border:1px solid #cbd5e1;border-radius:6px;font:inherit}
.api-practical13 button{cursor:pointer;padding:10px 14px;border:0;border-radius:6px;background:#2563eb;color:white;font:inherit}
.api-practical13 button.secondary{background:#475569}.api-practical13 button.danger{background:#b91c1c}.api-practical13 .api-message{margin:14px 0;padding:10px;border-radius:6px;display:none}.api-practical13 .api-message.ok{display:block;background:#dcfce7;color:#166534}.api-practical13 .api-message.bad{display:block;background:#fee2e2;color:#991b1b}
.api-practical13 .api-table-wrap{overflow-x:auto}.api-practical13 table{width:100%;border-collapse:collapse}.api-practical13 th,.api-practical13 td{padding:12px;border:1px solid #d1d5db;text-align:left}.api-practical13 th{background:#eff6ff;color:#123b68}.api-practical13 .api-actions{display:flex;gap:7px;flex-wrap:wrap}
@media(max-width:760px){.api-practical13{margin:15px;padding:14px}.api-practical13 .api-form{grid-template-columns:1fr}}
</style>
</head>
<body>
<section class="api-practical13">
<h1>Event Management — Fetch API</h1>
<p>Practical 13: List, add, update, and delete events asynchronously without reloading the page.</p>
<p><a href="admin_dashboard.php">← Back to Admin Dashboard</a></p>
<div id="apiMessage" class="api-message" role="status" aria-live="polite"></div>
<form id="eventForm" class="api-form">
<input type="hidden" id="eventId" value="">
<input id="eventTitle" type="text" maxlength="150" placeholder="Event title" required>
<select id="eventCategory" required><option value="">Choose category</option><option>Technical</option><option>Non-Technical</option></select>
<input id="eventDate" type="date" required>
<button type="submit" id="saveButton">Add Event</button>
</form>
<button type="button" class="secondary" id="cancelEdit" style="display:none">Cancel Edit</button>
<div class="api-table-wrap"><table><thead><tr><th>ID</th><th>Event</th><th>Category</th><th>Date</th><th>Actions</th></tr></thead><tbody id="eventRows"><tr><td colspan="5">Loading events...</td></tr></tbody></table></div>
</section>
<script>
const API_URL = 'event_api.php';
const CSRF_TOKEN = '<?= $csrf ?>';
const form = document.getElementById('eventForm');
const messageBox = document.getElementById('apiMessage');
const rows = document.getElementById('eventRows');
const idInput = document.getElementById('eventId');
const titleInput = document.getElementById('eventTitle');
const categoryInput = document.getElementById('eventCategory');
const dateInput = document.getElementById('eventDate');
const saveButton = document.getElementById('saveButton');
const cancelButton = document.getElementById('cancelEdit');
let eventsCache = [];
function showMessage(message, success=true){messageBox.textContent=message;messageBox.className='api-message '+(success?'ok':'bad');}
async function apiRequest(method, payload=null){
    const options={method,headers:{'Accept':'application/json','X-CSRF-Token':CSRF_TOKEN}};
    if(payload!==null){options.headers['Content-Type']='application/json';options.body=JSON.stringify(payload);}
    const response=await fetch(API_URL,options);
    let result; try{result=await response.json();}catch(e){throw new Error('The server returned an invalid response.');}
    if(!response.ok||!result.success)throw new Error(result.message||'Request failed.');
    return result;
}
function addCell(row,value){const cell=document.createElement('td');cell.textContent=value??'';row.appendChild(cell);return cell;}
function renderEvents(events){
    rows.replaceChildren();
    if(!events.length){const tr=document.createElement('tr');const td=addCell(tr,'No events found. Add an event using the form above.');td.colSpan=5;rows.appendChild(tr);return;}
    events.forEach(event=>{
        const tr=document.createElement('tr');addCell(tr,event.id);addCell(tr,event.title);addCell(tr,event.category);addCell(tr,event.event_date);
        const actionCell=addCell(tr,'');actionCell.className='api-actions';
        const edit=document.createElement('button');edit.type='button';edit.className='secondary';edit.textContent='Edit';edit.addEventListener('click',()=>startEdit(event));
        const del=document.createElement('button');del.type='button';del.className='danger';del.textContent='Delete';del.addEventListener('click',()=>deleteEvent(event));
        actionCell.append(edit,del);rows.appendChild(tr);
    });
}
async function loadEvents(){rows.innerHTML='<tr><td colspan="5">Loading events...</td></tr>';try{const result=await apiRequest('GET');eventsCache=result.events||[];renderEvents(eventsCache);}catch(error){rows.innerHTML='<tr><td colspan="5">Could not load events.</td></tr>';showMessage(error.message,false);}}
function startEdit(event){idInput.value=event.id;titleInput.value=event.title;categoryInput.value=event.category;dateInput.value=String(event.event_date).slice(0,10);saveButton.textContent='Update Event';cancelButton.style.display='inline-block';titleInput.focus();}
function resetForm(){form.reset();idInput.value='';saveButton.textContent='Add Event';cancelButton.style.display='none';}
form.addEventListener('submit',async event=>{event.preventDefault();const payload={title:titleInput.value.trim(),category:categoryInput.value,event_date:dateInput.value};const id=idInput.value;if(!payload.title||!payload.category||!payload.event_date){showMessage('Please complete all fields.',false);return;}try{const result=id?await apiRequest('PUT',{...payload,id:Number(id)}):await apiRequest('POST',payload);showMessage(result.message,true);resetForm();await loadEvents();}catch(error){showMessage(error.message,false);}});
cancelButton.addEventListener('click',resetForm);
async function deleteEvent(event){if(!confirm('Delete "'+event.title+'"? This cannot be undone.'))return;try{const result=await apiRequest('DELETE',{id:Number(event.id)});showMessage(result.message,true);if(idInput.value===String(event.id))resetForm();await loadEvents();}catch(error){showMessage(error.message,false);}}
loadEvents();
</script>
</body>
</html>
