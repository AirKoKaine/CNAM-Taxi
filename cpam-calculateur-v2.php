<?php
/**
 * Code Privé + Toute reproduction interdite + Jean-Marc Meuret 2025 + Calculateur CPAMV2
 *
 * Shortcode : [cpam_calculateur2]
 * Barème : convention-cadre taxis (arrêté du 29/07/2025, annexe 2), en vigueur au 01/11/2025.
 * Règles de calcul et d'arrondi : cahier des charges Cnam de facturation B2 taxis (v2.1, 23/09/2025).
 * Forfait Grande ville : 12 villes + 92/93/94 + établissements validés par la Cnam au 01/04/2026.
 *
 * Clé Google Maps : intégrée ci-dessous (peut être remplacée par la constante
 * CPAM2_GMAPS_KEY définie dans wp-config.php).
 */
add_shortcode('cpam_calculateur2', function () {
  if (!defined('CPAM2_ENQ')) {
    define('CPAM2_ENQ', true);

    /* ================= CSS ================= */
    $css = <<<'CSS'
#cpam2-root{
  --c1:#0b63c4; --c1-600:#0855a7; --c1-700:#074a92;
  --bg:#f6f8fb; --card:#f7fbff; --bd:#e7edf4; --txt:#1f2937; --muted:#6b7280; --chip:#f3f6fb;
  --r-md:14px; --r-lg:18px; --shadow:0 12px 28px rgba(0,0,0,.07);
  --s3:14px; --s4:18px; --s5:22px; --s6:28px; --h:46px;
  --fz-13:13px; --fz-14:14px; --fz-145:14.5px; --fz-15:15px; --fz-16:16px; --fz-20:20px; --fz-22:22px;
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;
  color:var(--txt); background:var(--bg);
  -webkit-text-size-adjust:100%;
}
#cpam2-root .wrap{ max-width:860px; margin:0 auto; padding:var(--s6) var(--s4); }
#cpam2-root .card{ background:var(--card); border:1px solid var(--bd); border-radius:var(--r-lg); box-shadow:var(--shadow); padding:28px 30px; }
#cpam2-root .hdr{ text-align:center; margin-bottom:var(--s5); }
#cpam2-root .title{ margin:0; font-size:var(--fz-22); font-weight:800; color:#074a92; }
#cpam2-root .sub{ margin:6px 0 0; color:#6b7280; font-size:var(--fz-145); }

#cpam2-root .bar{ border:1px solid var(--bd); border-radius:var(--r-md); background:#fbfdff; padding:var(--s4); margin-top:var(--s4); }
#cpam2-root .grid{ display:grid; gap:var(--s4); min-width:0; }
#cpam2-root .g2{ grid-template-columns:1fr; } @media(min-width:760px){ #cpam2-root .g2{ grid-template-columns:1fr 1fr; } }
#cpam2-root .field{ display:flex; flex-direction:column; min-width:0; }
#cpam2-root .label{ font-size:var(--fz-14); font-weight:600; color:#334155; margin-bottom:8px; }
#cpam2-root .hint{ font-size:12px; color:var(--muted); margin-top:4px; line-height:1.4; }
#cpam2-root .input, #cpam2-root select, #cpam2-root input[type="number"], #cpam2-root input[type="text"]{
  width:100%; min-width:0; box-sizing:border-box; background:#fff; border:1px solid var(--bd); border-radius:12px;
  height:var(--h); padding:10px 12px; font-size:var(--fz-15); transition:border-color .2s, box-shadow .2s;
}
#cpam2-root .input:focus, #cpam2-root select:focus{ outline:none; border-color:var(--c1); box-shadow:0 0 0 3px rgba(11,99,196,.18); }
#cpam2-root input[type="number"]{ -moz-appearance:textfield; }
#cpam2-root input[type="number"]::-webkit-outer-spin-button,
#cpam2-root input[type="number"]::-webkit-inner-spin-button{ -webkit-appearance:none; margin:0; }

/* Badge Grande ville */
#cpam2-root .gv-badge{ display:inline-block; margin-top:10px; font-size:var(--fz-13); padding:6px 12px; border-radius:999px; background:#f3f4f6; color:#6b7280; }
#cpam2-root .gv-badge.on{ background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }

/* Cartes patients */
#cpam2-root .pass{ background:var(--card); border:1px solid var(--bd); border-radius:var(--r-md); overflow:hidden; }
#cpam2-root .pass .head{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px 16px; border-bottom:1px dashed var(--bd); min-width:0; }
#cpam2-root .pass .head h4{ margin:0; font-size:var(--fz-14); color:#455; }
#cpam2-root .pass .head .mini{ height:34px; padding:0 12px; border:1px solid var(--bd); background:#fff; color:#0b63c4; border-radius:10px; font-weight:700; font-size:var(--fz-13); cursor:pointer; }
#cpam2-root .blk{ padding:12px 16px; }
#cpam2-root .blk + .blk{ border-top:1px solid #eef3f8; }
#cpam2-root .options{ display:flex; flex-direction:column; gap:8px; }
#cpam2-root .check{ display:flex; align-items:flex-start; gap:10px; font-size:var(--fz-13); color:#374151; line-height:1.35; }
#cpam2-root .check input{ margin-top:2px; }
#cpam2-root .check.disabled{ opacity:.5; }

/* Actions */
#cpam2-root .actions{ margin-top:var(--s4); text-align:center; }
#cpam2-root .btn-group{ display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
#cpam2-root .btn{ display:inline-flex; align-items:center; justify-content:center; height:var(--h); padding:0 22px; border:none; border-radius:12px; background:var(--c1); color:#fff; font-weight:700; font-size:var(--fz-16); cursor:pointer; box-shadow:0 6px 14px rgba(11,99,196,.25); text-decoration:none; }
#cpam2-root .btn.secondary{ background:#fff; color:#0b63c4; border:1px solid var(--bd); box-shadow:none; }

/* Résultats */
#cpam2-root .result{ margin-top:var(--s5); padding-top:var(--s3); border-top:1px solid var(--bd); }
#cpam2-root .total{ font-size:var(--fz-20); font-weight:800; color:#074a92; margin-bottom:8px; }
#cpam2-root .chip{ display:inline-block; background:var(--chip); border:1px solid var(--bd); color:#344055; border-radius:999px; padding:7px 12px; margin:4px 6px 0 0; font-size:var(--fz-13); }
#cpam2-root .explain{ margin-top:var(--s4); padding:var(--s4); background:#f8fbff; border:1px solid var(--bd); border-radius:var(--r-md); font-size:var(--fz-14); line-height:1.6; color:#374151; }
#cpam2-root .explain:empty{ display:none; }
#cpam2-root .err{ color:#b91c1c; font-weight:600; }

/* Carte Google */
#cpam2-map{ width:100%; height:360px; min-height:300px; background:#eef3f8; border:1px solid var(--bd); border-radius:12px; overflow:hidden; display:none; margin-top:var(--s4); }
.pac-container{ z-index:100000 !important; }

/* Part AMO / reste à charge */
#cpam2-root .amo-box{ margin:10px 0 14px; padding:10px 12px; background:#fff; border:1px dashed var(--bd); border-radius:10px; display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
#cpam2-root .amo-pill{ background:#eef6ff; border:1px solid #d5e7ff; color:#074a92; border-radius:999px; padding:6px 10px; font-weight:700; font-size:var(--fz-13); }

/* ====== OPTI SMARTPHONE (<= 480px) : anti-débordement ====== */
@media (max-width: 480px){
  #cpam2-root{ --h:42px; }
  #cpam2-root .card{ padding:18px; border-radius:16px; }
  #cpam2-root .bar{ padding:12px; }
  #cpam2-root .grid{ gap:12px; }
  #cpam2-root .g2{ grid-template-columns:1fr; }
  #cpam2-root .blk{ padding:10px 12px; }
  #cpam2-root .label{ margin-bottom:6px; font-size:13.5px; }
  #cpam2-root .input, #cpam2-root select{ font-size:14px; }
  #cpam2-root .pass{ border-radius:12px; }
  #cpam2-root .pass .head{ flex-wrap:wrap; }
  #cpam2-root .pass .head .mini{ width:100%; margin-top:6px; }
  #cpam2-root .btn{ width:100%; }
  #cpam2-map{ height:300px; }
}
CSS;

    wp_register_style('cpam2-style', false);
    wp_enqueue_style('cpam2-style');
    wp_add_inline_style('cpam2-style', $css);

    /* ================= JS ================= */
    $js = <<<'JS'
(()=>{"use strict";
const $ = s => document.querySelector(s);
const $$ = s => Array.from(document.querySelectorAll(s));
/* ARRONDI(x;2) du cahier des charges Cnam : arrondi au centime, 0,005 → centime supérieur.
   toPrecision neutralise les erreurs binaires (ex. 15,015 calculé 15,01499999…). */
const r2 = x => Math.round(Number((x * 100).toPrecision(12))) / 100;
const fr = (x, d=2) => Number(x).toFixed(d).replace('.', ',');
const esc = s => String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const getRoundMode = () => (document.querySelector('input[name="cpam2-roundMode"]:checked')?.value) || 'down';

/* =====================================================================
   BARÈME — convention-cadre taxis (arrêté du 29/07/2025, annexe 2) et
   cahier des charges Cnam de facturation B2 taxis (v2.1 du 23/09/2025)
   ===================================================================== */
const T = {
  forfait: 13, forfaitGV: 28, kmInclus: 4,     // forfait PEC et accompagnement / + Grande ville (13 + 15)
  majoration: 0.50,                            // nuit OU dimanche/férié (non cumulables), sur forfait et km
  aVideMoins50: 0.25, aVide50Plus: 0.50, seuilAVide: 50,
  tpmr: 30,                                    // ni majoré ni abattu
  drom: 3,                                     // supplément forfaitaire : abattu, non majoré
  partage: {2:0.23, 3:0.35, 4:0.37},           // 4 à 8 patients : 37 %
  seulLongue: 0.05, seuilSeulMin: 30,          // seuil local, 30 km minimum
  franchise: 4, franchiseJour: 8               // franchise médicale (déduite du remboursement)
};

/* ================= FORFAIT GRANDE VILLE ================= */
const GV_VILLES = ['Marseille','Paris','Nice','Toulouse','Lyon','Strasbourg','Montpellier','Rennes','Bordeaux','Lille','Grenoble','Nantes'];
const GV_DEPTS = {'92':'Hauts-de-Seine','93':'Seine-Saint-Denis','94':'Val-de-Marne'};
/* Établissements validés par la Cnam au 01/04/2026
   [dépt, établissement, commune, FINESS géo, mot-clé distinctif (reconnu avec la commune)] */
const GV_ETAB = [
  ['34','Clinique St Jean Sud de France','St Jean de Vedas','340024314','clinique st jean'],
  ['34','Clinique du Parc','Castelnau le Lez','340780667','clinique du parc'],
  ['34','Nephrocare CL Parc Castelnau Newco 1','Castelnau le Lez','340780840','nephrocare'],
  ['34','Aider Santé UAD St Jean sud de France','St Jean de Vedas','340024553','aider'],
  ['34','Aider Santé UAD Grabels Cordier 1','Grabels','340013119','aider'],
  ['31','Clinique Croix du Sud et SSR attaché','Quint Fonsegrives','310026927','croix du sud'],
  ['31',"Clinique de l'Union",'St Jean','310780283',"clinique de l union"],
  ['31',"SSR attaché à la clinique de l'Union",'St Jean','310792635',"clinique de l union"],
  ['31','CL Néphro Exupéry UDM Union St Jean','St Jean','310026612','exupery'],
  ['31','CL Néphro Exupéry UAD UDM Quint Fonsegrives','Quint Fonsegrives','310031414','exupery'],
  ['38','CHU (site de La Tronche)','La Tronche','380000067','chu'],
  ['38',"CHU (site d'Échirolles)",'Echirolles','380782722','chu'],
  ['38','AGDUC (La Tronche)','La Tronche','380784801','agduc'],
  ['44','CHU Nantes site Laennec','Saint-Herblain','440017598','laennec'],
  ['44','Clinique Santé Atlantique','Saint-Herblain','440033819','sante atlantique'],
  ['44',"Centre d'hémodialyse du Confluent",'Rezé','440036499','confluent'],
  ['44','Hôpital Privé du Confluent','Rezé','440041580','confluent'],
  ['44','CHU Nantes ICO site Gauducheau','St Herblain','440001113','gauducheau'],
  ['44','Écho Dialyse','St Herblain','440055770','echo'],
  ['44','CRF Tourmaline','St Herblain','440000255','tourmaline'],
  ['44','GHT Maubreuil','St Herblain','440000024','maubreuil'],
  ['33','Nouvelle Clinique Bordeaux Tondu','Floirac','330781402','tondu'],
  ['33','Polyclinique Jean Villar','Bruges','330782582','jean villar'],
  ['33','Clinique Mutualiste de Pessac','Pessac','330780529','mutualiste'],
  ['33','Polyclinique Bordeaux Rive Droite','Lormont','330780263','rive droite'],
  ['33','CRF La Tour de Gassies','Bruges','330781139','gassies'],
  ['33','Hôpital Suburbain du Bouscat','Le Bouscat','330000332','suburbain'],
  ['33','Antenne autodialyse AURAD Le Haillan','Le Haillan','330058058','aurad'],
  ['33','Maison de Santé Protestante Bordeaux Bagatelle','Talence','330000340','bagatelle'],
  ['69','Infirmerie Protestante','Caluire et Cuire','690793468','infirmerie protestante'],
  ['69','Hôpital des Charpennes (HCL)','Villeurbanne','690784194','charpennes'],
  ['69','Hôpital Louis Pradel (HCL)','Bron','690784186','louis pradel'],
  ['69','Hôpital Pierre Wertheimer (HCL)','Bron','690784178','wertheimer'],
  ['69','Médipôle Hôpital Privé','Villeurbanne','690041124','medipole'],
  ['69','Médipôle Hôpital Mutualiste','Villeurbanne','690041132','medipole'],
  ['69','Hôpital Femme Mère Enfant (HCL)','Bron','690007539','femme mere enfant'],
  ['69','Le Vinatier Psy Univ Lyon Métropole','Bron','690000088','vinatier'],
  ['69',"Clinique du Val d'Ouest Vendôme",'Ecully','690780358',"val d ouest"],
  ['69','Clinique Médico-Chirurgicale Charcot','Ste Foy les Lyon','690780366','charcot'],
  ['59','Santélys site CHU Lille','Loos','590060901','santelys'],
  ['59','SSR pédiatrique Marc Sautelet','Villeneuve d Ascq','590782611','sautelet'],
  ['59','Unité de dialyse de Loos','Loos','590031738','dialyse'],
  ['59','Unité de dialyse de Faches-Thumesnil','Faches Thumesnil','590035200','dialyse'],
  ['59','Santélys unité de dialyse Lambersart','Lambersart','590035390','santelys'],
  ['59','Unité de dialyse de Mons-en-Barœul','Mons en Baroeul','590813341','dialyse'],
  ['59','GHICL Hôpital Saint-Philibert','Lomme','590780284','philibert'],
  ['59','Clinique de la Mitterie','Lomme','590806360','mitterie'],
  ['67','Centre Médico-Chirurgical Obstétrique (CMCO)','Schiltigheim','670780113','obstetrique'],
  ['67','UGECAM Alsace','Illkirch','670781129','ugecam'],
  ['67','Clinique du Ried','Schiltigheim','670002278','clinique du ried'],
  ['35','Clinique privée Sévigné','Cesson Sevigne','350005146','clinique'],
  ['35','Centre Hospitalier Privé Saint-Grégoire','St Gregoire','350000121','hospitalier prive'],
  ['35','UGECAM pôle gériatrique rennais','Chantepie','350005021','ugecam'],
  ['35','Centre de dialyse AUB Santé Montgermont','Montgermont','350046751','aub'],
  ['06','Institut Arnault Tzanck','Saint Laurent du Var','060780491','tzanck'],
  ['06',"Centre d'hémodialyse Institut Arnault Tzanck",'Saint Laurent du Var','060791860','tzanck'],
  ['06','Centre cardio médico-chirurgical Tzanck','Saint Laurent du Var','060794013','tzanck']
];
const norm = s => String(s||'').normalize('NFD').replace(/[̀-ͯ]/g,'').toLowerCase()
  .replace(/œ/g,'oe').replace(/[’'`\-–—_.,()]/g,' ').replace(/\bsaint\b/g,'st').replace(/\bsainte\b/g,'ste')
  .replace(/\s+/g,' ').trim();

/** Motif d'éligibilité au forfait Grande ville pour une adresse, sinon null. */
function gvReason(raw){
  const s = norm(raw); if(!s) return null;
  for(const e of GV_ETAB){
    if(s.includes(e[3]) || s.includes(norm(e[1]))) return 'établissement validé Cnam : '+e[1];
    if(s.includes(norm(e[4])) && s.includes(norm(e[2]))) return 'établissement validé Cnam : '+e[1]+' ('+e[2]+')';
  }
  const m = s.match(/\b(9[234])\d{3}\b/);
  if(m) return 'commune du '+m[1]+' ('+GV_DEPTS[m[1]]+')';
  if(/\b75\d{3}\b/.test(s)) return 'Paris';
  for(const v of GV_VILLES){
    const k = norm(v);
    if(s === k || new RegExp('\\b\\d{5} '+k+'\\b').test(s) || new RegExp('^'+k+' (\\d|cedex|centre)').test(s)
       || new RegExp('(^| )'+k+' (\\d{1,2}(e|er|eme) )?(france|$)').test(s)) return v;
  }
  return null;
}
function gvFromAddresses(extra){
  const from = [$('#cpam2-addrPickup')?.value, extra?.from].map(gvReason).find(Boolean) || null;
  const to   = [$('#cpam2-addrDrop')?.value,   extra?.to  ].map(gvReason).find(Boolean) || null;
  const reasons = [];
  if(from) reasons.push('prise en charge : '+from);
  if(to)   reasons.push('dépose : '+to);
  const b = $('#cpam2-gvBadge');
  if(b){
    b.className = 'gv-badge'+(reasons.length?' on':'');
    b.textContent = reasons.length ? 'Forfait Grande ville applicable ('+reasons.join(' / ')+')'
      : (($('#cpam2-addrPickup')?.value||$('#cpam2-addrDrop')?.value) ? 'Forfait Grande ville : aucun lieu éligible reconnu' : '');
    b.style.display = b.textContent ? '' : 'none';
  }
  return reasons.length > 0;
}

/* ================= JOURS FÉRIÉS (selon le département de l'ADS) ================= */
function easter(y){ // dimanche de Pâques (algorithme de Meeus)
  const a=y%19,b=Math.floor(y/100),c=y%100,d=Math.floor(b/4),e=b%4,f=Math.floor((b+8)/25),g=Math.floor((b-f+1)/3),
        h=(19*a+b-d-g+15)%30,i=Math.floor(c/4),k=c%4,l=(32+2*e+2*i-h-k)%7,m=Math.floor((a+11*h+22*l)/451),
        mo=Math.floor((h+l-7*m+114)/31),da=((h+l-7*m+114)%31)+1;
  return new Date(y,mo-1,da);
}
const ymd = d => d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
const addDays = (d,n) => new Date(d.getFullYear(), d.getMonth(), d.getDate()+n);
function holidays(y, dept){
  const p = easter(y);
  const list = { [y+'-01-01']:'Jour de l\'an', [ymd(addDays(p,1))]:'Lundi de Pâques', [y+'-05-01']:'Fête du travail',
    [y+'-05-08']:'Victoire 1945', [ymd(addDays(p,39))]:'Ascension', [ymd(addDays(p,50))]:'Lundi de Pentecôte',
    [y+'-07-14']:'Fête nationale', [y+'-08-15']:'Assomption', [y+'-11-01']:'Toussaint', [y+'-11-11']:'Armistice', [y+'-12-25']:'Noël' };
  if(['57','67','68'].includes(dept)){ list[ymd(addDays(p,-2))]='Vendredi saint'; list[y+'-12-26']='Saint-Étienne'; }
  const abolition = {'971':'05-27','972':'05-22','973':'06-10','974':'12-20','976':'04-27'};
  if(abolition[dept]) list[y+'-'+abolition[dept]]='Abolition de l\'esclavage';
  return list;
}

/** Période de réalisation (RG PERIODE DE REALISATION TRAJET 001 à 003 et % MAJORATION 002-003). */
function periodeAuto(dateStr, hDep, hArr, dept){
  if(!dateStr || !hDep) return null;
  const [Y,M,D] = dateStr.split('-').map(Number);
  const [h1,m1] = hDep.split(':').map(Number);
  const start = new Date(Y,M-1,D,h1,m1);
  let end = start;
  if(hArr){ const [h2,m2]=hArr.split(':').map(Number); end=new Date(Y,M-1,D,h2,m2); if(end<start) end=addDays(end,1); }
  // Nuit : plus de la moitié du temps en charge entre 20 h (inclus) et 8 h (exclu)
  const total = (end-start)/60000;
  let night = 0;
  if(total > 0){
    for(let t=new Date(start); t<end; t=new Date(t.getTime()+60000)){ const h=t.getHours(); if(h>=20||h<8) night++; }
    if(night > total/2) return {code:'NU', txt:'nuit (plus de la moitié du trajet entre 20 h et 8 h)'};
  } else if(h1>=20 || h1<8){
    return {code:'NU', txt:'nuit (prise en charge entre 20 h et 8 h)'};
  }
  // Dimanche / jour férié : prise en charge un samedi à partir de 12 h, un dimanche ou un jour férié
  const ferie = holidays(Y, dept)[ymd(start)];
  if(ferie) return {code:'DF', txt:'jour férié ('+ferie+')'};
  if(start.getDay()===0) return {code:'DF', txt:'dimanche'};
  if(start.getDay()===6 && h1>=12) return {code:'DF', txt:'samedi après 12 h'};
  return {code:'J', txt:'jour'};
}

/* ================= Google ================= */
let gMap=null,gDirRenderer=null,gDirService=null;
window.cpamCalcInit_v2=function(){
  const opts={componentRestrictions:{country:['fr','mc']}};
  try{
    ['#cpam2-addrPickup','#cpam2-addrDrop'].forEach(sel=>{
      const el=$(sel); if(!el) return;
      const ac=new google.maps.places.Autocomplete(el,opts);
      ac.addListener('place_changed',()=>{ const p=ac.getPlace()||{};
        el.dataset.full = (p.name && p.formatted_address && !el.value.includes(p.name)) ? p.name+', '+p.formatted_address : '';
        gvFromAddresses(); });
    });
  }catch(e){console.warn('Places non init',e);}
};
function ensureMap(){
  if(!gMap){
    const el=document.getElementById('cpam2-map');
    gMap=new google.maps.Map(el,{center:{lat:43.7034,lng:7.2663},zoom:11,mapTypeControl:false,streetViewControl:false,fullscreenControl:true});
    gDirRenderer=new google.maps.DirectionsRenderer({map:gMap});
    gDirService=new google.maps.DirectionsService();
  }
}

/* ================= Cartes patients ================= */
function patientCard(i){
  return `
  <div class="pass" data-p-index="${i}">
    <div class="head">
      <h4>Patient ${i}</h4>
      <button type="button" class="mini cpam2-resetOne" data-index="${i}">Réinitialiser ce patient</button>
    </div>

    <div class="blk"><div class="field"><span class="label">Nature du trajet</span>
      <div class="options">
        <label class="check"><input id="cpam2-p${i}-hosp" type="checkbox">
          En lien avec une hospitalisation : entrée ou sortie d'hospitalisation complète, partielle ou ambulatoire, ou séance de chimiothérapie, radiothérapie ou hémodialyse</label>
        <label class="check cpam2-p${i}-videRow" style="padding-left:24px"><input id="cpam2-p${i}-vide" type="checkbox">
          Avec un aller ou un retour effectué à vide (taxi venu à vide de sa station jusqu'à l'établissement, ou reparti à vide de l'établissement jusqu'à sa station)</label>
      </div>
      <div class="hint">Seul ce cas majore le tarif kilométrique (+25 % sous 50 km en charge, +50 % à partir de 50 km). Tout autre trajet (soins, examens, rendez-vous médical…) est facturé au tarif de base.</div>
    </div></div>

    <div class="blk"><div class="grid g2">
      <div class="field"><label class="label" for="cpam2-p${i}-km">Distance réellement parcourue en charge (km)</label>
        <input id="cpam2-p${i}-km" class="input" type="number" inputmode="decimal" step="1" min="0">
      </div>
      <div class="field"><label class="label" for="cpam2-p${i}-tolls">Péages du trajet (€, sur justificatif)</label>
        <input id="cpam2-p${i}-tolls" class="input" type="number" inputmode="decimal" step="0.01" min="0">
        <div class="cpam2-shared-only hint" style="display:none">
          Divisés entre <input id="cpam2-p${i}-tollShare" type="number" min="1" max="8" step="1" value="2" style="width:56px;height:30px;padding:2px 6px;display:inline-block"> patient(s) ayant emprunté le péage
        </div>
      </div>
    </div></div>

    <div class="blk"><div class="grid g2">
      <div class="field"><label class="label" for="cpam2-p${i}-date">Date de prise en charge</label>
        <input id="cpam2-p${i}-date" class="input" type="date">
      </div>
      <div class="field"><span class="label">Heure de départ / d'arrivée</span>
        <div style="display:flex;gap:8px"><input id="cpam2-p${i}-hDep" class="input" type="time" aria-label="Heure de départ"><input id="cpam2-p${i}-hArr" class="input" type="time" aria-label="Heure d'arrivée"></div>
      </div>
      <div class="field" style="grid-column:1 / -1"><label class="label" for="cpam2-p${i}-periode">Majoration +50 % (nuit ou dimanche/férié, non cumulables)</label>
        <select id="cpam2-p${i}-periode" class="input">
          <option value="auto" selected>Automatique d'après la date et les heures</option>
          <option value="J">Jour (pas de majoration)</option>
          <option value="NU">Nuit : plus de la moitié du trajet entre 20 h et 8 h</option>
          <option value="DF">Samedi dès 12 h, dimanche ou jour férié</option>
        </select>
        <div id="cpam2-p${i}-periodeInfo" class="hint"></div>
      </div>
    </div></div>

    <div class="blk"><div class="options">
      <label class="check"><input id="cpam2-p${i}-isGV" type="checkbox"> Forfait Grande ville (prise en charge et/ou dépose dans une commune ou un établissement éligible)</label>
      <label class="check"><input id="cpam2-p${i}-isTPMR" type="checkbox"> Supplément TPMR (véhicule spécialement équipé, +30 €)</label>
    </div></div>

    <div class="blk"><div class="grid g2">
      <div class="field"><label class="label" for="cpam2-p${i}-taux">Taux de prise en charge</label>
        <select id="cpam2-p${i}-taux" class="input">
          <option value="0.55" selected>55 % (cas général)</option>
          <option value="1">100 % (ALD avec incapacité, AT/MP, maternité, invalidité, C2S/AME…)</option>
        </select>
      </div>
      <div class="field"><span class="label">Franchise médicale</span>
        <label class="check"><input id="cpam2-p${i}-noFranchise" type="checkbox"> Patient exonéré (moins de 18 ans, maternité, C2S ou AME)</label>
      </div>
    </div></div>

    <div class="blk cpam2-shared-only" style="display:none"><div class="field">
      <label class="label" for="cpam2-p${i}-kmSeul">Distance parcourue seul dans le véhicule (km)</label>
      <input id="cpam2-p${i}-kmSeul" class="input" type="number" inputmode="decimal" step="1" min="0" placeholder="0">
      <div class="hint">À partir du seuil local (30 km minimum), l'abattement de ce patient est ramené à 5 %.</div>
    </div></div>

    <div class="blk cpam2-single-only"><div class="options">
      <label class="check"><input id="cpam2-p${i}-refus" type="checkbox"> Le patient a refusé le transport partagé qui lui était proposé</label>
    </div></div>
  </div>`;
}

function nbPatients(){ return parseInt($('#cpam2-nbPatients')?.value||'1',10); }
function deptCode(){ return $('#cpam2-dept')?.selectedOptions[0]?.dataset.code || '06'; }
function isDromDept(){ return !!$('#cpam2-dept')?.selectedOptions[0]?.dataset.drom; }
function seuilSeul(){ return Math.max(T.seuilSeulMin, parseFloat($('#cpam2-seuilSeul')?.value)||T.seuilSeulMin); }

function rebuildPatientIndex(){
  const nb=nbPatients();
  const sel=$('#cpam2-patientIndex'); if(!sel) return;
  const cur=parseInt(sel.value||'1',10);
  sel.innerHTML=Array.from({length:nb},(_,k)=>`<option value="${k+1}">Patient ${k+1}</option>`).join('');
  sel.value=String(Math.min(cur,nb));
}
function updatePatientUI(){
  const nb=nbPatients();
  $$('.cpam2-shared-only').forEach(el=> el.style.display = nb>=2 ? '' : 'none');
  $$('.cpam2-single-only').forEach(el=> el.style.display = nb>=2 ? 'none' : '');
  for(let i=1;i<=nb;i++){
    const hosp=$(`#cpam2-p${i}-hosp`), vide=$(`#cpam2-p${i}-vide`), row=$(`.cpam2-p${i}-videRow`);
    if(hosp && vide){ vide.disabled=!hosp.checked; if(!hosp.checked) vide.checked=false; row?.classList.toggle('disabled',!hosp.checked); }
    const ts=$(`#cpam2-p${i}-tollShare`); if(ts){ ts.max=String(nb); if(+ts.value>nb) ts.value=String(nb); }
    const info=$(`#cpam2-p${i}-periodeInfo`);
    if(info){
      const sel=$(`#cpam2-p${i}-periode`)?.value;
      const p=periodeAuto($(`#cpam2-p${i}-date`)?.value, $(`#cpam2-p${i}-hDep`)?.value, $(`#cpam2-p${i}-hArr`)?.value, deptCode());
      info.textContent = sel==='auto' ? (p ? 'Période détectée : '+p.txt : 'Renseignez la date et l\'heure de départ, sinon le trajet est compté de jour.') : '';
    }
  }
}

/* ================= Calcul (règles de gestion du cahier des charges Cnam) ================= */
function compute(){
  const deptOpt=$('#cpam2-dept').selectedOptions[0];
  const tarifBase=parseFloat(deptOpt?.value)||1.07;
  const dept=deptCode();
  const suppLocal=Math.max(0, parseFloat($('#cpam2-suppLocal')?.value)||0);
  const suppForf=r2((isDromDept()?T.drom:0) + suppLocal);
  const nb=nbPatients();
  const seuil=seuilSeul();

  const kms=[]; for(let i=1;i<=nb;i++){ kms[i]=parseFloat($(`#cpam2-p${i}-km`)?.value)||0; }
  const nbMalades=kms.filter(v=> v>0).length; // bénéficiaires de soins dans le véhicule

  let total=0, totalAMO=0; const details=[]; let outHTML=''; const errs=[];

  for(let i=1;i<=nb;i++){
    const dist=kms[i]||0;
    if(nb>1 && dist<=0) continue;
    const hosp=!!$(`#cpam2-p${i}-hosp`)?.checked;
    const aVide=hosp && !!$(`#cpam2-p${i}-vide`)?.checked;
    const isGV=!!$(`#cpam2-p${i}-isGV`)?.checked;
    const isTPMR=!!$(`#cpam2-p${i}-isTPMR`)?.checked;
    const tolls=Math.max(0, parseFloat($(`#cpam2-p${i}-tolls`)?.value)||0);
    const taux=parseFloat($(`#cpam2-p${i}-taux`)?.value||'0.55');
    const noFranchise=!!$(`#cpam2-p${i}-noFranchise`)?.checked;
    const refus=nb===1 && !!$(`#cpam2-p${i}-refus`)?.checked;

    // Période : NU ou DI/JF (non cumulables)
    const selP=$(`#cpam2-p${i}-periode`)?.value||'auto';
    const auto=periodeAuto($(`#cpam2-p${i}-date`)?.value, $(`#cpam2-p${i}-hDep`)?.value, $(`#cpam2-p${i}-hArr`)?.value, dept);
    const periode = selP==='auto' ? (auto?.code||'J') : selP;
    const periodeTxt = selP==='auto' ? (auto?.txt||'jour (date/heure non renseignées)') : ({J:'jour',NU:'nuit',DF:'samedi après 12 h, dimanche ou férié'}[selP]);
    const coefMaj = periode==='J' ? 0 : T.majoration;

    // 1. Forfait (FORFAIT-002/003)
    const forfaitBase = isGV ? T.forfaitGV : T.forfait;
    // 2. Tarif km et montant km (MONTANT KM-002 à 008)
    const distFact = Math.max(0, dist - T.kmInclus);
    let tarifKm = tarifBase, coefVide = 0;
    if(aVide && dist > T.kmInclus){ coefVide = dist < T.seuilAVide ? T.aVideMoins50 : T.aVide50Plus; tarifKm = r2(tarifBase*(1+coefVide)); }
    const kmBase = r2(tarifKm*distFact);
    // 3. Majoration nuit / dimanche-férié (% MAJORATION NU OU DI/JF-002/003)
    const forfaitMaj = coefMaj ? r2(forfaitBase*coefMaj) : 0;
    const kmMaj = coefMaj ? r2(r2(tarifKm*coefMaj)*distFact) : 0;
    // 4. Abattement transport partagé (ABATTEMENT partagé-001 à 005)
    const kmSeul = parseFloat($(`#cpam2-p${i}-kmSeul`)?.value)||0;
    if(nbMalades>=2 && kmSeul>dist) errs.push(`Patient ${i} : la distance parcourue seul dépasse la distance du trajet.`);
    const seulLong = nbMalades>=2 && kmSeul>=seuil && kmSeul<=dist;
    let abatt=0, abTxt='';
    if(nbMalades>=2){
      abatt = seulLong ? T.seulLongue : T.partage[Math.min(nbMalades,4)];
      abTxt = seulLong ? `patient seul ${fr(kmSeul,0)} km ≥ ${seuil} km : −5 %` : `transport partagé ${nbMalades} patients : −${Math.round(abatt*100)} %`;
    }
    const forfaitApres = r2((forfaitBase+forfaitMaj)*(1-abatt));
    const kmApres = r2((kmBase+kmMaj)*(1-abatt));
    const suppApres = r2(suppForf*(1-abatt));
    // 5. Péage (PEAGE-001 à 004) et TPMR (TPMR-001 à 003) : ni majorés ni abattus
    let tollDiv=1;
    if(nbMalades>=2){ tollDiv=parseInt($(`#cpam2-p${i}-tollShare`)?.value||'1',10); if(!(tollDiv>=1&&tollDiv<=nb)){ errs.push(`Patient ${i} : nombre de patients pour le péage invalide (1 à ${nb}).`); tollDiv=1; } }
    const peage = r2(tolls/tollDiv);
    const tpmr = isTPMR ? T.tpmr : 0;

    const tot = r2(forfaitApres + kmApres + suppApres + peage + tpmr);
    // Part AMO calculée poste par poste (FACTURE-006 à 011)
    const amo = [forfaitApres,kmApres,suppApres,peage,tpmr].reduce((s,v)=> r2(s + r2(v*taux)), 0);
    total=r2(total+tot); totalAMO=r2(totalAMO+amo);

    outHTML += `
      <div class="total">Patient ${i} : ${fr(tot)} €</div>
      <div class="chip">${isGV?'Forfait Grande ville 28 €':'Forfait 13 €'}</div>
      <div class="chip">${fr(distFact,0)} km × ${fr(tarifKm)} €${coefVide?` (+${Math.round(coefVide*100)} % hospitalisation à vide)`:''}</div>
      ${coefMaj?`<div class="chip">+50 % ${esc(periodeTxt)}</div>`:''}
      ${suppForf?`<div class="chip">Suppléments forfaitaires ${fr(suppForf)} €</div>`:''}
      ${abatt?`<div class="chip">${esc(abTxt)}</div>`:''}
      ${peage?`<div class="chip">Péage ${fr(peage)} €${tollDiv>1?` (${fr(tolls)} € ÷ ${tollDiv})`:''}</div>`:''}
      ${tpmr?'<div class="chip">TPMR 30 €</div>':''}
      ${refus?'<div class="chip">Refus du transport partagé : hors tiers payant</div>':''}
      <br/>`;

    const L=[];
    L.push(`• Forfait ${isGV?'prise en charge, accompagnement et Grande ville':'prise en charge et accompagnement'} (4 premiers km inclus) : ${fr(forfaitBase)} €`);
    if(coefVide) L.push(`• Trajet lié à une hospitalisation avec aller ou retour à vide : tarif km ${fr(tarifBase)} € × ${fr(1+coefVide)} = ${fr(tarifKm)} € (arrondi au centime)`);
    else L.push(`• Tarif kilométrique du département ${esc(dept)} : ${fr(tarifKm)} €/km${hosp?' (hospitalisation sans trajet à vide : pas de majoration)':''}`);
    L.push(`• Km facturés : ${fr(dist,0)} − 4 = ${fr(distFact,0)} km × ${fr(tarifKm)} € = ${fr(kmBase)} €`);
    if(coefMaj){
      L.push(`• Majoration ${esc(periodeTxt)} : forfait ${fr(forfaitBase)} € × 50 % = ${fr(forfaitMaj)} € ; km : ${fr(r2(tarifKm*coefMaj))} € × ${fr(distFact,0)} km = ${fr(kmMaj)} €`);
    } else L.push(`• Période : ${esc(periodeTxt)}, pas de majoration`);
    if(suppForf) L.push(`• Suppléments forfaitaires${isDromDept()?' (dont DROM 3 €)':''} : ${fr(suppForf)} € (non majorés)`);
    if(abatt){
      L.push(`• Abattement ${esc(abTxt)} : forfait ${fr(forfaitBase+forfaitMaj)} € → ${fr(forfaitApres)} € ; km ${fr(kmBase+kmMaj)} € → ${fr(kmApres)} €${suppForf?` ; suppléments ${fr(suppForf)} € → ${fr(suppApres)} €`:''}`);
    }
    if(peage) L.push(`• Péage${tollDiv>1?` (${fr(tolls)} € ÷ ${tollDiv} patients)`:''} : ${fr(peage)} € (ni majoré ni abattu)`);
    if(tpmr) L.push(`• Supplément TPMR : 30,00 € (ni majoré ni abattu)`);
    L.push(`→ Total patient ${i} : ${fr(tot)} € · part Assurance Maladie ${Math.round(taux*100)} % : ${fr(amo)} € · reste ${fr(r2(tot-amo))} €`);
    if(!noFranchise) L.push(`  Franchise médicale de 4 € déduite du remboursement du patient (plafond 8 €/jour par transporteur, 50 €/an).`);
    if(refus) L.push(`  Refus du transport partagé : facture hors tiers payant (le patient avance les frais), mention obligatoire sur la facture.`);
    details.push(`Patient ${i}\n`+L.join('\n'));
  }

  if(!details.length){ $('#cpam2-result').innerHTML='<div class="err">Renseignez la distance d\'au moins un patient.</div>'; $('#cpam2-explain').innerHTML=''; return; }

  const recap = `
    <div class="amo-box">
      <span class="amo-pill">Part Assurance Maladie : ${fr(totalAMO)} €</span>
      <span class="amo-pill">Reste (patient ou mutuelle) : ${fr(r2(total-totalAMO))} €</span>
    </div>`;

  $('#cpam2-result').innerHTML = (errs.length?`<div class="err">${errs.map(esc).join('<br>')}</div>`:'')
    + `<div class="total">Total ${nbMalades>=2?'des factures':'trajet'} : ${fr(total)} €</div>` + recap + outHTML;

  const rules=[];
  rules.push(`Barème unique depuis le 01/11/2025 : forfait de 13 € (28 € avec Grande ville) incluant 4 km, puis tarif kilométrique du département de l'ADS. Il n'existe plus de tarif « consultation » ou « hospitalisation » distinct.`);
  if(details.some(t=>t.includes('aller ou retour à vide :')))
    rules.push(`Hospitalisation (entrée/sortie, ambulatoire, chimio, radio, dialyse) avec aller ou retour à vide : seul le trajet en charge est facturé et son tarif km est majoré (+25 % sous 50 km, +50 % à partir de 50 km).`);
  if(details.some(t=>t.includes('Majoration ')))
    rules.push(`Majoration de 50 % sur le forfait et les km : nuit si plus de la moitié du trajet a lieu entre 20 h et 8 h, sinon samedi dès 12 h, dimanche ou jour férié. Pas de cumul.`);
  if(nbMalades>=2)
    rules.push(`Transport partagé : une facture par patient sur sa propre distance. L'abattement porte sur le forfait, les km, la majoration et les suppléments forfaitaires, jamais sur le péage ni sur le TPMR.`);

  const block = `**Règles appliquées**\n• `+rules.join('\n• ')+`\n\n**Détail de calcul**\n`+details.join('\n\n');
  $('#cpam2-explain').innerHTML = esc(block).replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>');
  const rl=$('#cpam2-rulesLink'); if(rl) rl.style.display='block';
}

/* ================= Distance Google + carte ================= */
function getPatientIndex(){ return parseInt($('#cpam2-patientIndex')?.value||'1',10); }
function setDistanceIntoPatient(km, isGV){
  const n=getPatientIndex(), el=$('#cpam2-p'+n+'-km'); if(!el) return false;
  el.value=String(km);
  const gv=$('#cpam2-p'+n+'-isGV'); if(gv) gv.checked=!!isGV;
  updatePatientUI(); compute(); return true;
}
async function computeDistance(){
  const out=$('#cpam2-distDisplay'), mapEl=$('#cpam2-map');
  const orig=$('#cpam2-addrPickup')?.value.trim(), dest=$('#cpam2-addrDrop')?.value.trim();
  if(!orig||!dest){ out.textContent='Renseignez le point de prise en charge ET le point de déchargement.'; return; }
  if(typeof google==='undefined' || !google.maps){ out.textContent='Google Maps n\'est pas chargé : saisissez la distance à la main.'; return; }
  try{
    ensureMap();
    const req={origin:orig, destination:dest, travelMode:google.maps.TravelMode.DRIVING, provideRouteAlternatives:true,
               drivingOptions:{departureTime:new Date()},
               avoidTolls:!!$('#cpam2-avoidTolls')?.checked, avoidHighways:!!$('#cpam2-avoidHighways')?.checked};
    const res=await gDirService.route(req); if(!res?.routes?.length) throw new Error('Aucun itinéraire trouvé');
    let bestIdx=0,bestMeters=Infinity;
    res.routes.forEach((r,idx)=>{let m=0;(r.legs||[]).forEach(l=>m+=(l.distance?.value||0)); if(m<bestMeters){bestMeters=m;bestIdx=idx;}});
    const kmRaw=(bestMeters/1000), kmRounded=(getRoundMode()==='up')?Math.ceil(kmRaw):Math.floor(kmRaw);
    const legs=res.routes[bestIdx].legs||[];
    const isGV=gvFromAddresses({
      from:[$('#cpam2-addrPickup').dataset.full, legs[0]?.start_address].filter(Boolean).join(' | '),
      to:[$('#cpam2-addrDrop').dataset.full, legs[legs.length-1]?.end_address].filter(Boolean).join(' | ')
    });
    out.textContent=`${kmRounded} km (trajet le plus court, arrondi ${(getRoundMode()==='down'?'inférieur':'supérieur')}) → patient ${getPatientIndex()}`
      + (isGV ? ' · forfait Grande ville coché' : '');
    setDistanceIntoPatient(kmRounded, isGV);
    mapEl.style.display='block'; gDirRenderer.setDirections(res);
    if(typeof gDirRenderer.setRouteIndex==='function') gDirRenderer.setRouteIndex(bestIdx);
    const b=new google.maps.LatLngBounds();
    legs.forEach(leg=>{b.extend(leg.start_location); b.extend(leg.end_location);});
    gMap.fitBounds(b); setTimeout(()=>{google.maps.event.trigger(gMap,'resize'); gMap.fitBounds(b);},150);
  }catch(e){ out.textContent='Erreur de calcul : '+(e.message||'inconnue'); }
}

/* ================= Réinitialisation ================= */
function resetPatient(i){
  const root=$(`.pass[data-p-index="${i}"]`); if(!root) return;
  root.querySelectorAll('input').forEach(el=>{ if(el.type==='checkbox') el.checked=false; else if(!/tollShare$/.test(el.id)) el.value=''; });
  root.querySelectorAll('select').forEach(el=>{ el.selectedIndex = el.querySelector('option[selected]') ? Array.from(el.options).findIndex(o=>o.defaultSelected) : 0; });
  const ts=root.querySelector(`#cpam2-p${i}-tollShare`); if(ts) ts.value=String(nbPatients());
  updatePatientUI(); compute();
}
function hardReset(){
  const opt=$('#cpam2-dept').querySelector('option[data-code="06"]'); if(opt) opt.selected=true;
  $('#cpam2-nbPatients').value='1';
  const sl=$('#cpam2-suppLocal'); if(sl) sl.value='0';
  const ss=$('#cpam2-seuilSeul'); if(ss) ss.value='30';
  ['cpam2-addrPickup','cpam2-addrDrop'].forEach(id=>{const el=document.getElementById(id); if(el){ el.value=''; el.dataset.full=''; }});
  ['cpam2-avoidTolls','cpam2-avoidHighways'].forEach(id=>{const el=document.getElementById(id); if(el) el.checked=false;});
  document.querySelector('input[name="cpam2-roundMode"][value="down"]').checked=true;
  const mapEl=$('#cpam2-map'); if(mapEl) mapEl.style.display='none'; if(gDirRenderer) gDirRenderer.set('directions',null);
  regen(); $('#cpam2-patientIndex').value='1';
  $('#cpam2-result').innerHTML=''; $('#cpam2-explain').innerHTML=''; $('#cpam2-distDisplay').textContent='';
  const rl=$('#cpam2-rulesLink'); if(rl) rl.style.display='none';
  gvFromAddresses(); updateDromInfo();
}
function updateDromInfo(){ const el=$('#cpam2-dromInfo'); if(el) el.style.display=isDromDept()?'':'none'; }

/* ================= Démarrage et événements ================= */
function regen(){
  const nb=nbPatients();
  const box=$('#cpam2-forms'); box.innerHTML='';
  for(let i=1;i<=nb;i++) box.insertAdjacentHTML('beforeend', patientCard(i));
  for(let i=1;i<=nb;i++){ const ts=$(`#cpam2-p${i}-tollShare`); if(ts) ts.value=String(nb); }
  rebuildPatientIndex(); updatePatientUI();
}
document.addEventListener('DOMContentLoaded', ()=>{
  if(!$('#cpam2-root')) return;
  regen(); updateDromInfo(); gvFromAddresses();
  $('#cpam2-nbPatients')?.addEventListener('change', ()=>{
    const before=getPatientIndex();
    const saved={}; $$('#cpam2-forms input, #cpam2-forms select').forEach(el=>{ saved[el.id]=(el.type==='checkbox') ? el.checked : el.value; });
    regen();
    $$('#cpam2-forms input, #cpam2-forms select').forEach(el=>{ if(el.id in saved && !/tollShare$/.test(el.id)){ if(el.type==='checkbox') el.checked=saved[el.id]; else el.value=saved[el.id]; } });
    updatePatientUI();
    $('#cpam2-patientIndex').value=String(Math.min(before, nbPatients()));
    if($('#cpam2-result').innerHTML) compute();
  });
  ['#cpam2-dept','#cpam2-suppLocal','#cpam2-seuilSeul'].forEach(s=> $(s)?.addEventListener('change', ()=>{ updateDromInfo(); updatePatientUI(); if($('#cpam2-result').innerHTML) compute(); }));
  ['#cpam2-addrPickup','#cpam2-addrDrop'].forEach(s=> $(s)?.addEventListener('input', e=>{ e.target.dataset.full=''; gvFromAddresses(); }));
  $('#cpam2-calcBtn')?.addEventListener('click', e=>{e.preventDefault(); compute();});
  $('#cpam2-btnDistance')?.addEventListener('click', e=>{e.preventDefault(); computeDistance();});
  $$('.cpam2-resetAll').forEach(b=> b.addEventListener('click', e=>{ e.preventDefault(); hardReset(); }));
  document.addEventListener('click', ev=>{ const btn=ev.target.closest('.cpam2-resetOne'); if(!btn) return; ev.preventDefault(); resetPatient(parseInt(btn.dataset.index,10)||1); });
  const onEdit = ev=>{
    const id=ev.target?.id||'';
    if(!/^cpam2-p\d+-/.test(id)) return;
    updatePatientUI();
    if($('#cpam2-result').innerHTML) compute();
  };
  document.addEventListener('input', onEdit);
  document.addEventListener('change', onEdit);
});
})();
JS;

    wp_register_script('cpam2-js', false, array(), null, true);
    wp_enqueue_script('cpam2-js');
    wp_add_inline_script('cpam2-js', $js);

    /* Google Maps (Places + Directions) */
    $key = defined('CPAM2_GMAPS_KEY') ? CPAM2_GMAPS_KEY : 'AIzaSyCqyT4qHRHK9kkSSC66YqHJ_V_Z99OsJGc';
    if ($key !== '') {
      $url = 'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode($key) . '&libraries=places&language=fr&region=FR&callback=cpamCalcInit_v2';
      wp_enqueue_script('cpam2-gmaps', $url, array('cpam2-js'), null, true);
    }
  }

  /* ================= HTML ================= */
  ob_start(); ?>
  <div id="cpam2-root">
    <div class="wrap">
      <div class="card">
        <header class="hdr">
          <h1 class="title">Convention Cadre 2025</h1>
          <p class="sub">Calculateur Trajet Taxi Conventionné</p>
        </header>

        <!-- Bloc distance / affectation -->
        <section class="bar">
          <div class="grid g2">
            <div class="field"><label class="label" for="cpam2-addrPickup">Point de prise en charge</label>
              <input id="cpam2-addrPickup" class="input" type="text" placeholder="Ex. : Aéroport Nice, CHU Pasteur…" autocomplete="off">
            </div>
            <div class="field"><label class="label" for="cpam2-addrDrop">Point de déchargement</label>
              <input id="cpam2-addrDrop" class="input" type="text" placeholder="Ex. : Institut Arnault Tzanck, clinique…" autocomplete="off">
            </div>
          </div>
          <span id="cpam2-gvBadge" class="gv-badge" style="display:none"></span>
          <div class="grid g2" style="margin-top:8px">
            <label class="check"><input type="checkbox" id="cpam2-avoidTolls"> Éviter les péages</label>
            <label class="check"><input type="checkbox" id="cpam2-avoidHighways"> Éviter les autoroutes</label>
          </div>
          <div class="grid g2" style="margin-top:8px; align-items:end;">
            <div class="field" style="grid-column:1 / -1;">
              <label class="label" for="cpam2-patientIndex">Patient à renseigner</label>
              <select id="cpam2-patientIndex" class="input" style="max-width:460px"></select>
            </div>
            <div class="field" style="grid-column:1 / -1;">
              <span class="label">Arrondi de la distance (à l'entier)</span>
              <div class="grid" style="grid-template-columns:repeat(2,minmax(140px,220px));gap:12px;">
                <label class="check"><input type="radio" name="cpam2-roundMode" value="down" checked> Inférieur (↘︎)</label>
                <label class="check"><input type="radio" name="cpam2-roundMode" value="up"> Supérieur (↗︎)</label>
              </div>
            </div>
          </div>
          <div class="actions">
            <div class="btn-group">
              <button id="cpam2-btnDistance" type="button" class="btn">Calculer la distance</button>
              <button type="button" class="btn secondary cpam2-resetAll">Réinitialiser</button>
            </div>
          </div>
          <div id="cpam2-distDisplay" class="explain" aria-live="polite"></div>
          <div class="map-wrap"><div id="cpam2-map"></div></div>
        </section>

        <!-- Filtres généraux -->
        <section class="bar">
          <div class="grid g2">
            <div class="field">
              <label class="label" for="cpam2-dept">Département de l'ADS</label>
              <select id="cpam2-dept" class="input">
                <option value="1.13" data-code="01">01 Ain — 1,13 €/km</option>
                <option value="1.20" data-code="02">02 Aisne — 1,20 €/km</option>
                <option value="1.19" data-code="03">03 Allier — 1,19 €/km</option>
                <option value="1.14" data-code="04">04 Alpes-de-Haute-Provence — 1,14 €/km</option>
                <option value="1.18" data-code="05">05 Hautes-Alpes — 1,18 €/km</option>
                <option value="1.27" data-code="06" selected>06 Alpes-Maritimes — 1,27 €/km</option>
                <option value="1.17" data-code="07">07 Ardèche — 1,17 €/km</option>
                <option value="1.17" data-code="08">08 Ardennes — 1,17 €/km</option>
                <option value="1.15" data-code="09">09 Ariège — 1,15 €/km</option>
                <option value="1.13" data-code="10">10 Aube — 1,13 €/km</option>
                <option value="1.08" data-code="11">11 Aude — 1,08 €/km</option>
                <option value="1.16" data-code="12">12 Aveyron — 1,16 €/km</option>
                <option value="1.10" data-code="13">13 Bouches-du-Rhône — 1,10 €/km</option>
                <option value="1.07" data-code="14">14 Calvados — 1,07 €/km</option>
                <option value="1.13" data-code="15">15 Cantal — 1,13 €/km</option>
                <option value="1.12" data-code="16">16 Charente — 1,12 €/km</option>
                <option value="1.10" data-code="17">17 Charente-Maritime — 1,10 €/km</option>
                <option value="1.26" data-code="18">18 Cher — 1,26 €/km</option>
                <option value="1.16" data-code="19">19 Corrèze — 1,16 €/km</option>
                <option value="1.12" data-code="21">21 Côte-d'Or — 1,12 €/km</option>
                <option value="1.13" data-code="22">22 Côtes-d'Armor — 1,13 €/km</option>
                <option value="1.18" data-code="23">23 Creuse — 1,18 €/km</option>
                <option value="1.11" data-code="24">24 Dordogne — 1,11 €/km</option>
                <option value="1.08" data-code="25">25 Doubs — 1,08 €/km</option>
                <option value="1.16" data-code="26">26 Drôme — 1,16 €/km</option>
                <option value="1.21" data-code="27">27 Eure — 1,21 €/km</option>
                <option value="1.19" data-code="28">28 Eure-et-Loir — 1,19 €/km</option>
                <option value="1.07" data-code="29">29 Finistère — 1,07 €/km</option>
                <option value="1.27" data-code="2A">2A Corse-du-Sud — 1,27 €/km</option>
                <option value="1.27" data-code="2B">2B Haute-Corse — 1,27 €/km</option>
                <option value="1.08" data-code="30">30 Gard — 1,08 €/km</option>
                <option value="1.10" data-code="31">31 Haute-Garonne — 1,10 €/km</option>
                <option value="1.19" data-code="32">32 Gers — 1,19 €/km</option>
                <option value="1.07" data-code="33">33 Gironde — 1,07 €/km</option>
                <option value="1.07" data-code="34">34 Hérault — 1,07 €/km</option>
                <option value="1.07" data-code="35">35 Ille-et-Vilaine — 1,07 €/km</option>
                <option value="1.25" data-code="36">36 Indre — 1,25 €/km</option>
                <option value="1.18" data-code="37">37 Indre-et-Loire — 1,18 €/km</option>
                <option value="1.22" data-code="38">38 Isère — 1,22 €/km</option>
                <option value="1.11" data-code="39">39 Jura — 1,11 €/km</option>
                <option value="1.13" data-code="40">40 Landes — 1,13 €/km</option>
                <option value="1.13" data-code="41">41 Loir-et-Cher — 1,13 €/km</option>
                <option value="1.08" data-code="42">42 Loire — 1,08 €/km</option>
                <option value="1.24" data-code="43">43 Haute-Loire — 1,24 €/km</option>
                <option value="1.08" data-code="44">44 Loire-Atlantique — 1,08 €/km</option>
                <option value="1.07" data-code="45">45 Loiret — 1,07 €/km</option>
                <option value="1.13" data-code="46">46 Lot — 1,13 €/km</option>
                <option value="1.11" data-code="47">47 Lot-et-Garonne — 1,11 €/km</option>
                <option value="1.25" data-code="48">48 Lozère — 1,25 €/km</option>
                <option value="1.08" data-code="49">49 Maine-et-Loire — 1,08 €/km</option>
                <option value="1.16" data-code="50">50 Manche — 1,16 €/km</option>
                <option value="1.12" data-code="51">51 Marne — 1,12 €/km</option>
                <option value="1.26" data-code="52">52 Haute-Marne — 1,26 €/km</option>
                <option value="1.09" data-code="53">53 Mayenne — 1,09 €/km</option>
                <option value="1.09" data-code="54">54 Meurthe-et-Moselle — 1,09 €/km</option>
                <option value="1.12" data-code="55">55 Meuse — 1,12 €/km</option>
                <option value="1.07" data-code="56">56 Morbihan — 1,07 €/km</option>
                <option value="1.14" data-code="57">57 Moselle — 1,14 €/km</option>
                <option value="1.27" data-code="58">58 Nièvre — 1,27 €/km</option>
                <option value="1.20" data-code="59">59 Nord — 1,20 €/km</option>
                <option value="1.20" data-code="60">60 Oise — 1,20 €/km</option>
                <option value="1.17" data-code="61">61 Orne — 1,17 €/km</option>
                <option value="1.20" data-code="62">62 Pas-de-Calais — 1,20 €/km</option>
                <option value="1.08" data-code="63">63 Puy-de-Dôme — 1,08 €/km</option>
                <option value="1.14" data-code="64">64 Pyrénées-Atlantiques — 1,14 €/km</option>
                <option value="1.07" data-code="65">65 Hautes-Pyrénées — 1,07 €/km</option>
                <option value="1.18" data-code="66">66 Pyrénées-Orientales — 1,18 €/km</option>
                <option value="1.07" data-code="67">67 Bas-Rhin — 1,07 €/km</option>
                <option value="1.07" data-code="68">68 Haut-Rhin — 1,07 €/km</option>
                <option value="1.07" data-code="69">69 Rhône — 1,07 €/km</option>
                <option value="1.08" data-code="70">70 Haute-Saône — 1,08 €/km</option>
                <option value="1.10" data-code="71">71 Saône-et-Loire — 1,10 €/km</option>
                <option value="1.07" data-code="72">72 Sarthe — 1,07 €/km</option>
                <option value="1.15" data-code="73">73 Savoie — 1,15 €/km</option>
                <option value="1.22" data-code="74">74 Haute-Savoie — 1,22 €/km</option>
                <option value="1.22" data-code="75">75 Paris — 1,22 €/km</option>
                <option value="1.18" data-code="76">76 Seine-Maritime — 1,18 €/km</option>
                <option value="1.07" data-code="77">77 Seine-et-Marne — 1,07 €/km</option>
                <option value="1.07" data-code="78">78 Yvelines — 1,07 €/km</option>
                <option value="1.08" data-code="79">79 Deux-Sèvres — 1,08 €/km</option>
                <option value="1.15" data-code="80">80 Somme — 1,15 €/km</option>
                <option value="1.07" data-code="81">81 Tarn — 1,07 €/km</option>
                <option value="1.07" data-code="82">82 Tarn-et-Garonne — 1,07 €/km</option>
                <option value="1.16" data-code="83">83 Var — 1,16 €/km</option>
                <option value="1.20" data-code="84">84 Vaucluse — 1,20 €/km</option>
                <option value="1.07" data-code="85">85 Vendée — 1,07 €/km</option>
                <option value="1.11" data-code="86">86 Vienne — 1,11 €/km</option>
                <option value="1.10" data-code="87">87 Haute-Vienne — 1,10 €/km</option>
                <option value="1.10" data-code="88">88 Vosges — 1,10 €/km</option>
                <option value="1.11" data-code="89">89 Yonne — 1,11 €/km</option>
                <option value="1.08" data-code="90">90 Territoire de Belfort — 1,08 €/km</option>
                <option value="1.07" data-code="91">91 Essonne — 1,07 €/km</option>
                <option value="1.07" data-code="92">92 Hauts-de-Seine — 1,07 €/km</option>
                <option value="1.07" data-code="93">93 Seine-Saint-Denis — 1,07 €/km</option>
                <option value="1.07" data-code="94">94 Val-de-Marne — 1,07 €/km</option>
                <option value="1.07" data-code="95">95 Val-d'Oise — 1,07 €/km</option>
                <option value="1.07" data-code="971" data-drom="1">971 Guadeloupe — 1,07 €/km</option>
                <option value="1.20" data-code="972" data-drom="1">972 Martinique — 1,20 €/km</option>
                <option value="1.10" data-code="973" data-drom="1">973 Guyane — 1,10 €/km</option>
                <option value="1.22" data-code="974" data-drom="1">974 Réunion — 1,22 €/km</option>
                <option value="1.10" data-code="976" data-drom="1">976 Mayotte — 1,10 €/km</option>
              </select>
              <div id="cpam2-dromInfo" class="hint" style="display:none">ADS en outre-mer : supplément DROM de 3 € ajouté automatiquement à chaque trajet.</div>
            </div>
            <div class="field">
              <label class="label" for="cpam2-nbPatients">Nombre de patients dans le véhicule (transport partagé si 2 ou plus)</label>
              <select id="cpam2-nbPatients" class="input">
                <option>1</option><option>2</option><option>3</option><option>4</option>
                <option>5</option><option>6</option><option>7</option><option>8</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="cpam2-seuilSeul">Seuil local « patient seul » (km)</label>
              <input id="cpam2-seuilSeul" class="input" type="number" min="30" step="1" value="30">
              <div class="hint">Fixé par la convention locale avec la CPAM de l'ADS (30 km minimum).</div>
            </div>
            <div class="field">
              <label class="label" for="cpam2-suppLocal">Supplément local validé par la Cnam (€ par trajet)</label>
              <input id="cpam2-suppLocal" class="input" type="number" min="0" step="0.01" value="0">
              <div class="hint">Uniquement si votre convention locale en prévoit un (sinon 0).</div>
            </div>
          </div>
        </section>

        <!-- Fiches patients -->
        <div id="cpam2-forms" class="grid" style="margin-top:var(--s4)"></div>

        <!-- Actions globales -->
        <div class="actions">
          <div class="btn-group">
            <button type="button" id="cpam2-calcBtn" class="btn">Calculer</button>
            <button type="button" class="btn secondary cpam2-resetAll">Réinitialiser</button>
          </div>
        </div>

        <!-- Résultats -->
        <section class="result">
          <div id="cpam2-result" aria-live="polite"></div>
          <div id="cpam2-explain" class="explain"></div>
          <div id="cpam2-rulesLink" style="text-align:center; margin-top:14px; display:none;">
            <a href="https://cpam.taxiapp.club/regle-de-calcul/" target="_blank" rel="noopener" class="btn cpam2-rules-btn" role="button">Règles de calcul</a>
          </div>
          <p class="hint" style="text-align:center;margin-top:14px">Barème : convention-cadre taxis du 29/07/2025 (en vigueur au 01/11/2025) et cahier des charges Cnam de facturation B2 taxis. Liste Grande ville : Cnam, 01/04/2026. Estimation indicative : seule la facture validée par la CPAM fait foi.</p>
        </section>
      </div>
    </div>
  </div>
  <?php
  return ob_get_clean();
});
