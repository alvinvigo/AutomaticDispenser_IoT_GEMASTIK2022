async function init(params) {
    var elem = document.getElementById("main-body-display-engine");
    var elemAdd = "";
    const arrData = await fetch("http://35.184.107.203/api/engine/getAll");
    const data1 = await arrData.json();
    console.log(data1);
    if (data1 != null) {
        data1.forEach(item => {
            elemAdd += "<tr><td>" + item.name + "</td><td>" + item.uid + "</td><td>" + item.address +"</td><td>"+ item.size +
                    " kg/liter </td><td>" + item.kind + "</td><td>" + item.pickTime + "</td></tr>"; 
        });
    }
    elem.innerHTML = elemAdd;
}init()