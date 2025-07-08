//add a - and + operator click on button
function mines1(mines)
{
    if(mines.innerHTML=='+')
    {
        mines.innerHTML='-'
    }
    else{
        mines.innerHTML='+'
    }
}
function mines2(mines)
{
    if(mines.innerHTML=='+')
    {
        mines.innerHTML='-'
    }
    else{
        mines.innerHTML='+'
    }
}
function mines3(mines)
{
    if(mines.innerHTML=='+')
    {
        mines.innerHTML='-'
    }
    else{
        mines.innerHTML='+'
    }
}
function mines4(mines)
{
    if(mines.innerHTML=='+')
    {
        mines.innerHTML='-'
    }
    else{
        mines.innerHTML='+'
    }
}
function mines5(mines)
{
    if(mines.innerHTML=='+')
    {
        mines.innerHTML='-'
    }
    else{
        mines.innerHTML='+'
    }
}
function allmines(m1,m2,m3,m4)
{
    if(m1!='+' || m2!='+' || m3!='+' || m4!='+')
    {
        m1.innerHTML="+"
        m2.innerHTML="+"
        m3.innerHTML="+"
        m4.innerHTML="+"
    }
}
//add a text in p tag click onn button
function para1(para)
{
    if(para.innerHTML=="")
    {
        para.innerHTML = "Yes, you can Carwale, we make payment easy. Just navigate to our Cart page and submit your information. Once our team receives it they will look it over and get back to you promptly.";

    }
    else if(para.innerHTML==" ")
    {
        para.innerHTML = "Yes, you can Carwale, we make payment easy. Just navigate to our Cart page and submit your information. Once our team receives it they will look it over and get back to you promptly.";

    }
    else{
        para.innerHTML=" "
    }
}
function para2(para)
{
    if(para.innerHTML=="")
    {
        para.innerHTML = "Yes, you can. Carwale is pleased to offer online car buying! Start your vehicle search right here on our Inventory page. Without ever leaving home, you can shop for your next vehicle and get pre-approved all on our site. Contact us if you are interested in a vehicle, and we'll bring it to you for a test drive.";

    }
    else if(para.innerHTML==" ")
    {
        para.innerHTML = "Yes, you can. Carwale is pleased to offer online car buying! Start your vehicle search right here on our Inventory page. Without ever leaving home, you can shop for your next vehicle and get pre-approved all on our site. Contact us if you are interested in a vehicle, and we'll bring it to you for a test drive.";

    }
    else{
        para.innerHTML=" "
    }
}
function para3(para)
{
    if(para.innerHTML=="")
    {
        para.innerHTML = "We offer an extensive selection of top-tier car brands available in bulk quantities. You can effortlessly find the car you desire by either using the search bar or by navigating to the 'Cars' section in our menu.";

    }
    else if(para.innerHTML==" ")
    {
        para.innerHTML = "We offer an extensive selection of top-tier car brands available in bulk quantities. You can effortlessly find the car you desire by either using the search bar or by navigating to the 'Cars' section in our menu.";

    }
    else{
        para.innerHTML=" "
    }
}
function para4(para)
{
    if(para.innerHTML=="")
    {
        para.innerHTML = "Yes. If you're in need of a quick check or procedure, we encourage you to take advantage of our express we can take care of it for you quickly and accurately.";

    }
    else if(para.innerHTML==" ")
    {
        para.innerHTML = "Yes. If you're in need of a quick check or procedure, we encourage you to take advantage of our express we can take care of it for you quickly and accurately.";

    }
    else{
        para.innerHTML=" "
    }
}
function para5(para)
{
    if(para.innerHTML=="")
    {
        para.innerHTML = "We offer a wide array of premium car brands and accessories for your exploration. You can access these options by navigating to the 'Brands' section.";

    }
    else if(para.innerHTML==" ")
    {
        para.innerHTML = "We offer a wide array of premium car brands and accessories for your exploration. You can access these options by navigating to the 'Brands' section.";

    }
    else{
        para.innerHTML=" "
    }
}
// ask a question container all funnction
function plus1() {
    const mines=document.getElementById("mines1");
    let m5=document.getElementById("mines5")
    let m2=document.getElementById("mines2");
    let m3=document.getElementById('mines3')
    let m4=document.getElementById("mines4");
    mines1(mines)
    allmines(m2,m3,m4,m5)
    const para = document.getElementById("plus_box1");
    para1(para)
    let t2=document.getElementById("plus_box2");
    let t3=document.getElementById("plus_box3");
    let t4=document.getElementById("plus_box4");
    let t5=document.getElementById("plus_box5");
    t2.innerHTML="";
    t3.innerHTML="";
    t4.innerHTML="";
    t5.innerHTML="";
}
function plus2() {
    const mines=document.getElementById("mines2")
    let m1=document.getElementById("mines1")
    let m5=document.getElementById("mines5");
    let m3=document.getElementById('mines3')
    let m4=document.getElementById("mines4");
    mines2(mines);
    allmines(m1,m3,m4,m5)
    const para = document.getElementById("plus_box2");
    para2(para);
    let t1=document.getElementById("plus_box1");
    let t3=document.getElementById("plus_box3");
    let t4=document.getElementById("plus_box4");
    let t5=document.getElementById("plus_box5");
    t1.innerHTML="";
    t3.innerHTML="";
    t4.innerHTML="";
    t5.innerHTML="";
}
function plus3() {
    const mines=document.getElementById("mines3")
    let m1=document.getElementById("mines1")
    let m2=document.getElementById("mines2");
    let m5=document.getElementById('mines5')
    let m4=document.getElementById("mines4");
    mines3(mines);
    allmines(m1,m2,m4,m5)
    const para = document.getElementById("plus_box3");
    para3(para);
    let t1=document.getElementById("plus_box1");
    let t2=document.getElementById("plus_box2");
    let t4=document.getElementById("plus_box4");
    let t5=document.getElementById("plus_box5");
    t1.innerHTML="";
    t2.innerHTML="";
    t4.innerHTML=""
    t5.innerHTML="";
}
function plus4() {
    const mines=document.getElementById("mines4")
    let m1=document.getElementById("mines1")
    let m2=document.getElementById("mines2");
    let m3=document.getElementById('mines3')
    let m5=document.getElementById("mines5");
    mines4(mines);
    allmines(m1,m2,m3,m5)
    const para = document.getElementById("plus_box4");
    para4(para);
    let t1=document.getElementById("plus_box1");
    let t2=document.getElementById("plus_box2");
    let t3=document.getElementById("plus_box3");
    let t5=document.getElementById("plus_box5");
    t1.innerHTML="";
    t3.innerHTML="";
    t2.innerHTML="";
    t5.innerHTML="";
}
function plus5() {
    const mines=document.getElementById("mines5")
    let m1=document.getElementById("mines1")
    let m2=document.getElementById("mines2");
    let m3=document.getElementById('mines3')
    let m4=document.getElementById("mines4");
    mines5(mines);
    allmines(m1,m2,m3,m4)
    const para = document.getElementById("plus_box5");
    para5(para);
    let t1=document.getElementById("plus_box1");
    let t2=document.getElementById("plus_box2");
    let t3=document.getElementById("plus_box3");
    let t4=document.getElementById("plus_box4");
    t1.innerHTML="";
    t3.innerHTML="";
    t4.innerHTML="";
    t2.innerHTML="";
}
