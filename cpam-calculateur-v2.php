<?php
/**
 * Code Privé + Toute reproduction interdite + Jean-Marc Meuret 2025 + Calculateur CPAM V3
 *
 * Shortcode : [cpam_calculateur2]
 * Barème : convention-cadre taxis (arrêté du 29/07/2025, annexe 2), en vigueur au 01/11/2025.
 * Règles de calcul et d'arrondi : cahier des charges Cnam de facturation B2 taxis (v2.1, 23/09/2025).
 * Forfait Grande ville : 12 villes + 92/93/94 + établissements validés par la Cnam au 01/04/2026.
 * Dispositions locales : conventions départementales publiées sur ameli.fr (relevé du 10/11/2026).
 * Clé Google Maps intégrée dans le fichier (ne pas la retirer).
 */
add_shortcode('cpam_calculateur2', function () {
  if (!defined('CPAM2_ENQ')) {
    define('CPAM2_ENQ', true);

    /* ================= CSS ================= */
    $css = <<<'CSS'
/* Mise en page : formulaire à gauche en sections séparées par des filets, facture à droite, fixe au défilement. */
#cpam2-root{
  --ink:#1b2430; --muted:#5b6675; --line:#dde3ea; --line-strong:#b9c2cc;
  --ground:#f4f6f8; --surface:#ffffff; --field:#ffffff;
  --accent:#0b5fb0; --accent-ink:#ffffff; --accent-soft:#e9f1fb;
  --good:#1a7f4b; --good-soft:#e6f4ec; --warn:#9a5b00; --warn-soft:#fff4e0; --bad:#b42318;
  --r:8px; --r-lg:12px;
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
  font-size:16px; line-height:1.5; color:var(--ink); background:var(--ground);
  -webkit-text-size-adjust:100%; font-variant-numeric:tabular-nums;
}
#cpam2-root *{box-sizing:border-box}
#cpam2-root .wrap{max-width:1180px;margin:0 auto;padding:28px 20px 96px}
#cpam2-root h1,#cpam2-root h2,#cpam2-root h3{margin:0;line-height:1.25;letter-spacing:-.01em}
#cpam2-root h1{font-size:26px;font-weight:600}
#cpam2-root h2{font-size:17px;font-weight:600}
#cpam2-root h3{font-size:15px;font-weight:600}
#cpam2-root p{margin:0}
#cpam2-root .muted{color:var(--muted)}
#cpam2-root .small{font-size:14px}

/* En-tête */
#cpam2-root .head{display:flex;flex-wrap:wrap;gap:16px 24px;align-items:flex-end;justify-content:space-between;padding-bottom:20px;border-bottom:1px solid var(--line)}
#cpam2-root .head .sub{color:var(--muted);margin-top:4px}
#cpam2-root .head .dept{display:flex;flex-direction:column;gap:6px;min-width:260px}

/* Deux colonnes */
#cpam2-root .cols{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:32px;align-items:start;margin-top:24px}
@media (max-width:980px){#cpam2-root .cols{grid-template-columns:1fr}}

/* Sections du formulaire */
#cpam2-root .sec{display:grid;grid-template-columns:180px minmax(0,1fr);gap:16px 28px;padding:24px 0;border-bottom:1px solid var(--line)}
#cpam2-root .sec:first-child{padding-top:8px}
#cpam2-root .sec > .lead h2{margin-bottom:4px}
#cpam2-root .sec > .lead p{font-size:14px;color:var(--muted)}
@media (max-width:700px){#cpam2-root .sec{grid-template-columns:1fr;gap:12px}}

#cpam2-root .row{display:grid;gap:14px 16px;grid-template-columns:repeat(auto-fit,minmax(180px,1fr))}
#cpam2-root .row.two{grid-template-columns:1fr 1fr}
#cpam2-root .row + .row{margin-top:14px}
#cpam2-root .field{display:flex;flex-direction:column;gap:6px;min-width:0}
#cpam2-root .field.full{grid-column:1 / -1}
#cpam2-root label.lbl,#cpam2-root .lbl{font-size:13px;font-weight:500;color:var(--muted)}
#cpam2-root .ctl{height:44px;padding:0 12px;border:1px solid var(--line-strong);border-radius:var(--r);background:var(--field);font:inherit;font-size:16px;color:var(--ink);width:100%;min-width:0}
#cpam2-root .ctl:focus{outline:2px solid var(--accent);outline-offset:1px;border-color:var(--accent)}
#cpam2-root input[type=number].ctl{-moz-appearance:textfield}
#cpam2-root input[type=number].ctl::-webkit-inner-spin-button,#cpam2-root input[type=number].ctl::-webkit-outer-spin-button{-webkit-appearance:none}
#cpam2-root .unit{position:relative}
#cpam2-root .unit .ctl{padding-right:40px}
#cpam2-root .unit span{position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:14px;color:var(--muted);pointer-events:none}
#cpam2-root .hint{font-size:13px;color:var(--muted);line-height:1.45}
#cpam2-root .hint.err{color:var(--bad)}

/* Cases */
#cpam2-root .check{display:flex;align-items:flex-start;gap:10px;font-size:15px;line-height:1.4;padding:6px 0;cursor:pointer}
#cpam2-root .check input{width:18px;height:18px;margin:2px 0 0;accent-color:var(--accent);flex:none}
#cpam2-root .check.off{opacity:.5}
#cpam2-root .checks{display:flex;flex-direction:column;gap:2px}

/* Segments (choix exclusifs) */
#cpam2-root .seg{display:inline-flex;border:1px solid var(--line-strong);border-radius:var(--r);overflow:hidden;background:var(--field);position:relative}
#cpam2-root .seg label{padding:0 14px;height:36px;display:flex;align-items:center;font-size:14px;cursor:pointer;border-right:1px solid var(--line);user-select:none}
#cpam2-root .seg label:last-child{border-right:0}
#cpam2-root .seg input{position:absolute;opacity:0;width:0;height:0}
#cpam2-root .seg input:checked + span{color:var(--accent);font-weight:600}
#cpam2-root .seg label:has(input:checked){background:var(--accent-soft)}

/* Boutons */
#cpam2-root .btn{height:44px;padding:0 18px;border-radius:var(--r);border:1px solid var(--accent);background:var(--accent);color:var(--accent-ink);font:inherit;font-size:15px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;white-space:nowrap;text-decoration:none;transition:background .12s,transform .06s}
#cpam2-root .btn:hover{background:#0a549c;color:var(--accent-ink)}
#cpam2-root .btn:active{transform:translateY(1px)}
#cpam2-root .btn.ghost{background:transparent;color:var(--accent);border-color:var(--line-strong)}
#cpam2-root .btn.ghost:hover{background:var(--accent-soft);color:var(--accent)}
#cpam2-root .btn.sm{height:36px;padding:0 12px;font-size:14px;font-weight:500}
#cpam2-root .btn:disabled{opacity:.5;cursor:default}
#cpam2-root .btn:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
#cpam2-root .actions{display:flex;flex-wrap:wrap;gap:10px;align-items:center}

/* Trajets et patients */
#cpam2-root .trip{border:1px solid var(--line);border-radius:var(--r-lg);background:var(--surface);overflow:hidden}
#cpam2-root .trip + .trip{margin-top:16px}
#cpam2-root .trip-head{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;padding:12px 16px;border-bottom:1px solid var(--line);background:var(--ground)}
#cpam2-root .trip-head h3{flex:1}
#cpam2-root .trip-body{padding:16px}
#cpam2-root .trip-body .block + .block{margin-top:18px;padding-top:18px;border-top:1px dashed var(--line)}
#cpam2-root .block-title{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}
#cpam2-root .block-title h4{margin:0;font-size:14px;font-weight:600;color:var(--muted)}
#cpam2-root .tags{display:flex;flex-wrap:wrap;gap:6px}
#cpam2-root .tag{display:inline-flex;align-items:center;gap:6px;font-size:13px;padding:3px 9px;border-radius:999px;border:1px solid var(--line);background:var(--surface);color:var(--muted)}
#cpam2-root .tag.on{background:var(--good-soft);border-color:#b7e0c6;color:var(--good)}
#cpam2-root .tag.warn{background:var(--warn-soft);border-color:#f3d9a4;color:var(--warn)}
#cpam2-root .pat{display:grid;grid-template-columns:28px minmax(0,1fr);gap:12px;padding:12px 0;border-top:1px solid var(--line)}
#cpam2-root .pat:first-of-type{border-top:0;padding-top:0}
#cpam2-root .pat .n{width:28px;height:28px;border-radius:50%;background:var(--ink);color:#fff;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;margin-top:24px}
#cpam2-root .pat .grid{display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(150px,1fr))}
#cpam2-root .pat .opts{display:flex;flex-wrap:wrap;gap:2px 20px;margin-top:4px}
#cpam2-root .map{height:300px;border:1px solid var(--line);border-radius:var(--r);margin-top:14px;display:none;background:var(--ground)}
.pac-container{z-index:100000 !important}

/* Facture */
#cpam2-root .bill{position:sticky;top:16px;border:1px solid var(--line);border-radius:var(--r-lg);background:var(--surface);box-shadow:0 1px 2px rgba(27,36,48,.04),0 8px 24px rgba(27,36,48,.06)}
#cpam2-root .bill-head{padding:16px 20px;border-bottom:1px solid var(--line);display:flex;align-items:baseline;justify-content:space-between;gap:12px}
#cpam2-root .bill-head .total{font-size:28px;font-weight:600;letter-spacing:-.02em}
#cpam2-root .bill-body{padding:8px 20px 12px;max-height:calc(100vh - 260px);overflow:auto}
#cpam2-root .bill-group{padding:12px 0;border-bottom:1px solid var(--line)}
#cpam2-root .bill-group:last-child{border-bottom:0}
#cpam2-root .bill-group h3{font-size:14px;color:var(--muted);font-weight:600;margin-bottom:6px}
#cpam2-root .line{display:flex;justify-content:space-between;gap:12px;font-size:14px;padding:3px 0}
#cpam2-root .line.sub{color:var(--muted);padding-left:12px}
#cpam2-root .line.pat-line{font-weight:600;padding-top:8px;color:var(--ink)}
#cpam2-root .line.sum{font-weight:600;border-top:1px solid var(--line);margin-top:6px;padding-top:8px;font-size:15px;color:var(--ink)}
#cpam2-root .line .amt{white-space:nowrap}
#cpam2-root .line .neg{color:var(--good)}
#cpam2-root .bill-foot{padding:14px 20px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:12px}
#cpam2-root .split{display:grid;grid-template-columns:1fr 1fr;gap:10px}
#cpam2-root .split div{padding:10px 12px;border-radius:var(--r);background:var(--ground)}
#cpam2-root .split .lbl{display:block;margin-bottom:2px}
#cpam2-root .split b{font-size:18px;font-weight:600}
#cpam2-root .empty{padding:24px 0;color:var(--muted);font-size:15px}
#cpam2-root .rules{font-size:13px;color:var(--muted);line-height:1.5;padding:0 20px 16px;border-top:1px solid var(--line)}
#cpam2-root .rules summary{cursor:pointer;font-weight:500;color:var(--ink);padding:10px 0 4px}
#cpam2-root .rules ul{margin:6px 0 0;padding-left:18px}
#cpam2-root .foot{margin-top:28px;font-size:13px;color:var(--muted);line-height:1.5}

/* Barre basse sur mobile */
#cpam2-root .bar{display:none}
@media (max-width:980px){
  #cpam2-root .bill{position:static}
  #cpam2-root .bill-body{max-height:none}
  #cpam2-root .bar{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:50;background:var(--surface);border-top:1px solid var(--line);padding:10px 16px calc(10px + env(safe-area-inset-bottom,0px));align-items:center;justify-content:space-between;gap:12px}
  #cpam2-root .bar b{font-size:20px}
  #cpam2-root .wrap{padding-left:16px;padding-right:16px}
  #cpam2-root .row.two{grid-template-columns:1fr}
}
@media (prefers-reduced-motion:reduce){#cpam2-root *{transition:none !important}}
CSS;

    wp_register_style('cpam2-style', false);
    wp_enqueue_style('cpam2-style');
    wp_add_inline_style('cpam2-style', $css);

    /* ================= JS ================= */
    $js = <<<'JS'
(()=>{"use strict";
const root=document.getElementById('cpam2-root'); if(!root) return;
const $=(s,c=root)=>c.querySelector(s), $$=(s,c=root)=>Array.from(c.querySelectorAll(s));
/* ARRONDI(x;2) du cahier des charges Cnam : au centime, 0,005 vers le haut. */
const r2=x=>Math.round(Number((x*100).toPrecision(12)))/100;
const fr=(x,d=2)=>Number(x).toFixed(d).replace('.',',');
const eur=x=>fr(x)+' €';
const esc=s=>String(s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const num=v=>{const n=parseFloat(String(v??'').replace(',','.'));return isFinite(n)?n:0;};
const pad=n=>String(n).padStart(2,'0');
const ymd=d=>d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());
const hm=d=>pad(d.getHours())+':'+pad(d.getMinutes());
const addDays=(d,n)=>new Date(d.getFullYear(),d.getMonth(),d.getDate()+n);

/* ===================== BARÈME ===================== */
const T={forfait:13,forfaitGV:28,kmInclus:4,majoration:.5,aVideMoins50:.25,aVide50Plus:.5,seuilAVide:50,tpmr:30,drom:3,
  partage:{2:.23,3:.35,4:.37},seulLongue:.05,seuilSeulMin:30,franchise:4};
const COURTE={montant:2,maxKm:10,depuis:'2026-08-01'};
/* Tarif km par département de l'ADS (convention-cadre, annexe) */
const DEPTS=`01|Ain|1.13;02|Aisne|1.20;03|Allier|1.19;04|Alpes-de-Haute-Provence|1.14;05|Hautes-Alpes|1.18;06|Alpes-Maritimes|1.27;07|Ardèche|1.17;08|Ardennes|1.17;09|Ariège|1.15;10|Aube|1.13;11|Aude|1.08;12|Aveyron|1.16;13|Bouches-du-Rhône|1.10;14|Calvados|1.07;15|Cantal|1.13;16|Charente|1.12;17|Charente-Maritime|1.10;18|Cher|1.26;19|Corrèze|1.16;21|Côte-d'Or|1.12;22|Côtes-d'Armor|1.13;23|Creuse|1.18;24|Dordogne|1.11;25|Doubs|1.08;26|Drôme|1.16;27|Eure|1.21;28|Eure-et-Loir|1.19;29|Finistère|1.07;2A|Corse-du-Sud|1.27;2B|Haute-Corse|1.27;30|Gard|1.08;31|Haute-Garonne|1.10;32|Gers|1.19;33|Gironde|1.07;34|Hérault|1.07;35|Ille-et-Vilaine|1.07;36|Indre|1.25;37|Indre-et-Loire|1.18;38|Isère|1.22;39|Jura|1.11;40|Landes|1.13;41|Loir-et-Cher|1.13;42|Loire|1.08;43|Haute-Loire|1.24;44|Loire-Atlantique|1.08;45|Loiret|1.07;46|Lot|1.13;47|Lot-et-Garonne|1.11;48|Lozère|1.25;49|Maine-et-Loire|1.08;50|Manche|1.16;51|Marne|1.12;52|Haute-Marne|1.26;53|Mayenne|1.09;54|Meurthe-et-Moselle|1.09;55|Meuse|1.12;56|Morbihan|1.07;57|Moselle|1.14;58|Nièvre|1.27;59|Nord|1.20;60|Oise|1.20;61|Orne|1.17;62|Pas-de-Calais|1.20;63|Puy-de-Dôme|1.08;64|Pyrénées-Atlantiques|1.14;65|Hautes-Pyrénées|1.07;66|Pyrénées-Orientales|1.18;67|Bas-Rhin|1.07;68|Haut-Rhin|1.07;69|Rhône|1.07;70|Haute-Saône|1.08;71|Saône-et-Loire|1.10;72|Sarthe|1.07;73|Savoie|1.15;74|Haute-Savoie|1.22;75|Paris|1.22;76|Seine-Maritime|1.18;77|Seine-et-Marne|1.07;78|Yvelines|1.07;79|Deux-Sèvres|1.08;80|Somme|1.15;81|Tarn|1.07;82|Tarn-et-Garonne|1.07;83|Var|1.16;84|Vaucluse|1.20;85|Vendée|1.07;86|Vienne|1.11;87|Haute-Vienne|1.10;88|Vosges|1.10;89|Yonne|1.11;90|Territoire de Belfort|1.08;91|Essonne|1.07;92|Hauts-de-Seine|1.07;93|Seine-Saint-Denis|1.07;94|Val-de-Marne|1.07;95|Val-d'Oise|1.07;971|Guadeloupe|1.07;972|Martinique|1.20;973|Guyane|1.10;974|Réunion|1.22;976|Mayotte|1.10`
  .split(';').map(s=>{const [c,n,t]=s.split('|');return {code:c,name:n,rate:parseFloat(t),drom:c.length===3};});
const deptOf=c=>DEPTS.find(d=>d.code===c)||DEPTS[5];

/* Dispositions locales relevées sur ameli.fr (10/11/2026) */
const LOCAL_DEFAULT={seuilSeul:30,courteDistance:true,verifie:'',notes:[]};
const LOCAL={
  '06':{verifie:'CPAM 06, supplément courte distance confirmé par l\'entreprise (convention locale non publiée)',notes:[]},
  '13':{verifie:'convention CPAM 13 (màj 03/07/2026)',notes:['Péages urbains non pris en charge, sauf tunnel Prado-Carénage s\'il réduit le kilométrage (jamais Prado Sud).','Contrôle du kilométrage sur le distancier ViaMichelin.']},
  '24':{verifie:'convention CPAM 24 (30/09/2025, màj 07/2026)',notes:['Aller/retour à vide : la prescription cochée « hospitalisation » suffit comme justificatif ; sinon fournir le bulletin de situation.','Télépéage : bordereau mensuel à déposer sur cpam24.fr, mention « Télépéage » sur la facture.']},
  '34':{verifie:'convention CPAM 34 (01/08/2026)',notes:[]},
  '44':{verifie:'convention CPAM 44 (01/11/2025)',notes:[]},
  '69':{verifie:'convention CPAM 69 (11/2025) et FAQ du 30/12/2025',notes:['Aller/retour à vide refusé si l\'établissement est dans la même zone que l\'ADS (ex. ADS Lyon ou ZUPC vers un hôpital de Lyon, Bron, Vénissieux).','Bulletin de situation exigé pour la majoration à vide.','Contrôle du kilométrage sur Google Maps.']},
  '74':{verifie:'convention CPAM 74 (11/2025)',notes:['Supplément courte distance absent de la version consultée (antérieure au 01/08/2026) : à confirmer.','Péages : abonnement autoroutier à répercuter, bordereaux à conserver 2 ans.']},
  '75':{verifie:'convention CPAM 75 (2025-2029)',notes:['Supplément courte distance sans objet pour les trajets avec forfait Grande ville (Paris).']}
};
const localRules=d=>Object.assign({},LOCAL_DEFAULT,LOCAL[d]||{});

/* Forfait Grande ville */
const GV_VILLES=['Marseille','Paris','Nice','Toulouse','Lyon','Strasbourg','Montpellier','Rennes','Bordeaux','Lille','Grenoble','Nantes'];
const GV_ETAB=[
  ['34','Clinique St Jean Sud de France','St Jean de Vedas','340024314','clinique st jean'],['34','Clinique du Parc','Castelnau le Lez','340780667','clinique du parc'],
  ['34','Nephrocare CL Parc Castelnau Newco 1','Castelnau le Lez','340780840','nephrocare'],['34','Aider Santé UAD St Jean sud de France','St Jean de Vedas','340024553','aider'],
  ['34','Aider Santé UAD Grabels Cordier 1','Grabels','340013119','aider'],['31','Clinique Croix du Sud et SSR attaché','Quint Fonsegrives','310026927','croix du sud'],
  ['31',"Clinique de l'Union",'St Jean','310780283',"clinique de l union"],['31',"SSR attaché à la clinique de l'Union",'St Jean','310792635',"clinique de l union"],
  ['31','CL Néphro Exupéry UDM Union St Jean','St Jean','310026612','exupery'],['31','CL Néphro Exupéry UAD UDM Quint Fonsegrives','Quint Fonsegrives','310031414','exupery'],
  ['38','CHU (site de La Tronche)','La Tronche','380000067','chu'],['38',"CHU (site d'Échirolles)",'Echirolles','380782722','chu'],['38','AGDUC (La Tronche)','La Tronche','380784801','agduc'],
  ['44','CHU Nantes site Laennec','Saint-Herblain','440017598','laennec'],['44','Clinique Santé Atlantique','Saint-Herblain','440033819','sante atlantique'],
  ['44',"Centre d'hémodialyse du Confluent",'Rezé','440036499','confluent'],['44','Hôpital Privé du Confluent','Rezé','440041580','confluent'],
  ['44','CHU Nantes ICO site Gauducheau','St Herblain','440001113','gauducheau'],['44','Écho Dialyse','St Herblain','440055770','echo'],
  ['44','CRF Tourmaline','St Herblain','440000255','tourmaline'],['44','GHT Maubreuil','St Herblain','440000024','maubreuil'],
  ['33','Nouvelle Clinique Bordeaux Tondu','Floirac','330781402','tondu'],['33','Polyclinique Jean Villar','Bruges','330782582','jean villar'],
  ['33','Clinique Mutualiste de Pessac','Pessac','330780529','mutualiste'],['33','Polyclinique Bordeaux Rive Droite','Lormont','330780263','rive droite'],
  ['33','CRF La Tour de Gassies','Bruges','330781139','gassies'],['33','Hôpital Suburbain du Bouscat','Le Bouscat','330000332','suburbain'],
  ['33','Antenne autodialyse AURAD Le Haillan','Le Haillan','330058058','aurad'],['33','Maison de Santé Protestante Bordeaux Bagatelle','Talence','330000340','bagatelle'],
  ['69','Infirmerie Protestante','Caluire et Cuire','690793468','infirmerie protestante'],['69','Hôpital des Charpennes (HCL)','Villeurbanne','690784194','charpennes'],
  ['69','Hôpital Louis Pradel (HCL)','Bron','690784186','louis pradel'],['69','Hôpital Pierre Wertheimer (HCL)','Bron','690784178','wertheimer'],
  ['69','Médipôle Hôpital Privé','Villeurbanne','690041124','medipole'],['69','Médipôle Hôpital Mutualiste','Villeurbanne','690041132','medipole'],
  ['69','Hôpital Femme Mère Enfant (HCL)','Bron','690007539','femme mere enfant'],['69','Le Vinatier Psy Univ Lyon Métropole','Bron','690000088','vinatier'],
  ['69',"Clinique du Val d'Ouest Vendôme",'Ecully','690780358',"val d ouest"],['69','Clinique Médico-Chirurgicale Charcot','Ste Foy les Lyon','690780366','charcot'],
  ['59','Santélys site CHU Lille','Loos','590060901','santelys'],['59','SSR pédiatrique Marc Sautelet','Villeneuve d Ascq','590782611','sautelet'],
  ['59','Unité de dialyse de Loos','Loos','590031738','dialyse'],['59','Unité de dialyse de Faches-Thumesnil','Faches Thumesnil','590035200','dialyse'],
  ['59','Santélys unité de dialyse Lambersart','Lambersart','590035390','santelys'],['59','Unité de dialyse de Mons-en-Barœul','Mons en Baroeul','590813341','dialyse'],
  ['59','GHICL Hôpital Saint-Philibert','Lomme','590780284','philibert'],['59','Clinique de la Mitterie','Lomme','590806360','mitterie'],
  ['67','Centre Médico-Chirurgical Obstétrique (CMCO)','Schiltigheim','670780113','obstetrique'],['67','UGECAM Alsace','Illkirch','670781129','ugecam'],
  ['67','Clinique du Ried','Schiltigheim','670002278','clinique du ried'],['35','Clinique privée Sévigné','Cesson Sevigne','350005146','clinique'],
  ['35','Centre Hospitalier Privé Saint-Grégoire','St Gregoire','350000121','hospitalier prive'],['35','UGECAM pôle gériatrique rennais','Chantepie','350005021','ugecam'],
  ['35','Centre de dialyse AUB Santé Montgermont','Montgermont','350046751','aub'],
  ['06','Institut Arnault Tzanck','Saint Laurent du Var','060780491','tzanck'],['06',"Centre d'hémodialyse Institut Arnault Tzanck",'Saint Laurent du Var','060791860','tzanck'],
  ['06','Centre cardio médico-chirurgical Tzanck','Saint Laurent du Var','060794013','tzanck']];
const norm=s=>String(s||'').normalize('NFD').replace(/[̀-ͯ]/g,'').toLowerCase().replace(/œ/g,'oe').replace(/[’'`\-–—_.,()]/g,' ')
  .replace(/\bsaint\b/g,'st').replace(/\bsainte\b/g,'ste').replace(/\s+/g,' ').trim();
function gvReason(raw){
  const s=norm(raw); if(!s) return null;
  for(const e of GV_ETAB){
    if(s.includes(e[3])||s.includes(norm(e[1]))) return e[1];
    if(s.includes(norm(e[4]))&&s.includes(norm(e[2]))) return e[1]+' ('+e[2]+')';
  }
  const m=s.match(/\b(9[234])\d{3}\b/); if(m) return 'commune du '+m[1];
  if(/\b75\d{3}\b/.test(s)) return 'Paris';
  for(const v of GV_VILLES){const k=norm(v);
    if(s===k||new RegExp('\\b\\d{5} '+k+'\\b').test(s)||new RegExp('^'+k+' (\\d|cedex|centre)').test(s)||new RegExp('(^| )'+k+' (\\d{1,2}(e|er|eme) )?(france|$)').test(s)) return v;}
  return null;
}

/* Jours fériés selon le département */
function easter(y){const a=y%19,b=Math.floor(y/100),c=y%100,d=Math.floor(b/4),e=b%4,f=Math.floor((b+8)/25),g=Math.floor((b-f+1)/3),h=(19*a+b-d-g+15)%30,i=Math.floor(c/4),k=c%4,l=(32+2*e+2*i-h-k)%7,m=Math.floor((a+11*h+22*l)/451),mo=Math.floor((h+l-7*m+114)/31),da=((h+l-7*m+114)%31)+1;return new Date(y,mo-1,da);}
function holidays(y,dept){const p=easter(y);const L={[y+'-01-01']:'Jour de l\'an',[ymd(addDays(p,1))]:'Lundi de Pâques',[y+'-05-01']:'Fête du travail',[y+'-05-08']:'Victoire 1945',[ymd(addDays(p,39))]:'Ascension',[ymd(addDays(p,50))]:'Lundi de Pentecôte',[y+'-07-14']:'Fête nationale',[y+'-08-15']:'Assomption',[y+'-11-01']:'Toussaint',[y+'-11-11']:'Armistice',[y+'-12-25']:'Noël'};
  if(['57','67','68'].includes(dept)){L[ymd(addDays(p,-2))]='Vendredi saint';L[y+'-12-26']='Saint-Étienne';}
  const ab={'971':'05-27','972':'05-22','973':'06-10','974':'12-20','976':'04-27'}; if(ab[dept]) L[y+'-'+ab[dept]]='Abolition de l\'esclavage'; return L;}
/* Période de réalisation (RG PERIODE 001-003, % MAJORATION 002-003) */
function periodeAuto(dateStr,hDep,hArr,dept){
  if(!dateStr||!hDep) return null;
  const [Y,M,D]=dateStr.split('-').map(Number),[h1,m1]=hDep.split(':').map(Number);
  const start=new Date(Y,M-1,D,h1,m1); let end=start;
  if(hArr){const [h2,m2]=hArr.split(':').map(Number);end=new Date(Y,M-1,D,h2,m2);if(end<start) end=addDays(end,1);}
  const total=(end-start)/60000; let night=0;
  if(total>0){for(let t=new Date(start);t<end;t=new Date(t.getTime()+60000)){const h=t.getHours();if(h>=20||h<8) night++;} if(night>total/2) return {code:'NU',txt:'nuit (plus de la moitié du trajet entre 20 h et 8 h)'};}
  else if(h1>=20||h1<8) return {code:'NU',txt:'nuit (prise en charge entre 20 h et 8 h)'};
  const f=holidays(Y,dept)[ymd(start)]; if(f) return {code:'DF',txt:'jour férié ('+f+')'};
  if(start.getDay()===0) return {code:'DF',txt:'dimanche'};
  if(start.getDay()===6&&h1>=12) return {code:'DF',txt:'samedi après 12 h'};
  return {code:'J',txt:'jour'};
}

/* ===================== ÉTAT ===================== */
let uid=1;
const now=new Date();
function newPatient(){return {id:uid++,km:'',kmSeul:'',peage:'',peageDiv:'',tpmr:false,taux:'0.55',noFranchise:false,refus:false};}
function newTrip(label){return {id:uid++,label,from:'',to:'',fromFull:'',toFull:'',date:ymd(now),hDep:hm(now),hArr:'',periode:'auto',hosp:false,vide:false,gvAuto:true,gv:false,distance:null,patients:[newPatient()]};}
const S={dept:'06',round:'down',avoidTolls:false,avoidHighways:false,courteDistance:true,cdAuto:true,seuilSeul:'',suppLocal:'',trips:[newTrip('Aller')]};
try{const saved=JSON.parse(localStorage.getItem('cpam2.prefs')||'{}');['dept','round','courteDistance','cdAuto','suppLocal'].forEach(k=>{if(k in saved) S[k]=saved[k];});}catch(e){}
function savePrefs(){try{localStorage.setItem('cpam2.prefs',JSON.stringify({dept:S.dept,round:S.round,courteDistance:S.courteDistance,cdAuto:S.cdAuto,suppLocal:S.suppLocal}));}catch(e){}}
const seuil=()=>Math.max(T.seuilSeulMin,num(S.seuilSeul)||localRules(S.dept).seuilSeul);
function tripGV(t){
  const from=gvReason(t.fromFull)||gvReason(t.from), to=gvReason(t.toFull)||gvReason(t.to);
  return {auto:!!(from||to),reasons:[from&&'prise en charge : '+from,to&&'dépose : '+to].filter(Boolean)};
}
const tripIsGV=t=>t.gvAuto?tripGV(t).auto:t.gv;

/* ===================== CALCUL (règles de gestion Cnam) ===================== */
function computeTrip(t){
  const dept=deptOf(S.dept), isGV=tripIsGV(t);
  const auto=periodeAuto(t.date,t.hDep,t.hArr,S.dept);
  const periode=t.periode==='auto'?(auto?.code||'J'):t.periode;
  const periodeTxt=t.periode==='auto'?(auto?.txt||'jour (heure non renseignée)'):({J:'jour',NU:'nuit',DF:'samedi après 12 h, dimanche ou férié'}[t.periode]);
  const coefMaj=periode==='J'?0:T.majoration;
  const nbMal=t.patients.filter(p=>num(p.km)>0).length;
  const out={label:t.label,periodeTxt,isGV,gvReasons:tripGV(t).reasons,nbMal,patients:[],total:0,amo:0,errors:[]};
  t.patients.forEach((p,idx)=>{
    const dist=num(p.km); if(!(dist>0)) return;
    const aVide=t.hosp&&t.vide, taux=parseFloat(p.taux)||.55;
    const cd=(S.courteDistance&&!isGV&&dist<COURTE.maxKm&&(!t.date||t.date>=COURTE.depuis))?COURTE.montant:0;
    const suppForf=r2((dept.drom?T.drom:0)+cd+num(S.suppLocal));
    const forfaitBase=isGV?T.forfaitGV:T.forfait;
    const distFact=Math.max(0,dist-T.kmInclus);
    let tarifKm=dept.rate,coefVide=0;
    if(aVide&&dist>T.kmInclus){coefVide=dist<T.seuilAVide?T.aVideMoins50:T.aVide50Plus;tarifKm=r2(dept.rate*(1+coefVide));}
    const kmBase=r2(tarifKm*distFact);
    const forfaitMaj=coefMaj?r2(forfaitBase*coefMaj):0;
    const kmMaj=coefMaj?r2(r2(tarifKm*coefMaj)*distFact):0;
    const kmSeul=num(p.kmSeul);
    if(nbMal>=2&&kmSeul>dist) out.errors.push(`Patient ${idx+1} : la distance parcourue seul dépasse la distance du trajet.`);
    const seulLong=nbMal>=2&&kmSeul>=seuil()&&kmSeul<=dist;
    let abatt=0,abTxt='';
    if(nbMal>=2){abatt=seulLong?T.seulLongue:T.partage[Math.min(nbMal,4)];abTxt=seulLong?`Patient seul sur ${fr(kmSeul,0)} km (seuil ${seuil()} km) : abattement 5 %`:`Transport partagé, ${nbMal} patients : abattement ${Math.round(abatt*100)} %`;}
    const forfaitApres=r2((forfaitBase+forfaitMaj)*(1-abatt)), kmApres=r2((kmBase+kmMaj)*(1-abatt)), suppApres=r2(suppForf*(1-abatt));
    let div=1; if(nbMal>=2){div=parseInt(p.peageDiv,10)||nbMal; if(div<1||div>t.patients.length){out.errors.push(`Patient ${idx+1} : nombre de patients pour le péage invalide.`);div=1;}}
    const peage=r2(num(p.peage)/div), tpmr=p.tpmr?T.tpmr:0;
    const tot=r2(forfaitApres+kmApres+suppApres+peage+tpmr);
    const amo=[forfaitApres,kmApres,suppApres,peage,tpmr].reduce((s,v)=>r2(s+r2(v*taux)),0);
    out.total=r2(out.total+tot); out.amo=r2(out.amo+amo);
    out.patients.push({n:idx+1,dist,distFact,tarifKm,coefVide,forfaitBase,forfaitMaj,kmBase,kmMaj,coefMaj,suppForf,cd,abatt,abTxt,forfaitApres,kmApres,suppApres,peage,peageBrut:num(p.peage),div,tpmr,tot,amo,taux,franchise:p.noFranchise?0:T.franchise,refus:nbMal<2&&p.refus});
  });
  return out;
}
function computeAll(){const trips=S.trips.map(computeTrip);return {trips,total:r2(trips.reduce((s,t)=>s+t.total,0)),amo:r2(trips.reduce((s,t)=>s+t.amo,0)),errors:trips.flatMap(t=>t.errors.map(e=>t.label+' : '+e))};}

/* ===================== RENDU ===================== */
function renderStatic(){
  $('#cpam2-dept').innerHTML=DEPTS.map(d=>`<option value="${d.code}"${d.code===S.dept?' selected':''}>${d.code} ${esc(d.name)} · ${fr(d.rate)} €/km</option>`).join('');
  $('#cpam2-suppLocal').value=S.suppLocal||'';
  const rb=$(`input[name="cpam2-round"][value="${S.round}"]`); if(rb) rb.checked=true;
  renderLocal();
}
function renderLocal(){
  const L=localRules(S.dept), d=deptOf(S.dept);
  if(!S.seuilSeul) $('#cpam2-seuil').value=String(L.seuilSeul);
  if(S.cdAuto) S.courteDistance=!!L.courteDistance;
  $('#cpam2-cd').checked=S.courteDistance;
  const lines=[`Tarif kilométrique ${fr(d.rate)} €/km${d.drom?', supplément DROM de 3 € par trajet':''}.`];
  lines.push(L.verifie?`Dispositions locales : ${esc(L.verifie)}.`:`Convention locale du ${esc(S.dept)} non publiée sur ameli.fr : valeurs nationales (seuil 30 km, supplément courte distance diffusé par la Cnam au 01/08/2026). À confirmer auprès de votre CPAM.`);
  L.notes.forEach(n=>lines.push(esc(n)));
  $('#cpam2-local').innerHTML=lines.map(l=>`<p>${l}</p>`).join('');
}
function patientHTML(t,p,idx){
  const shared=t.patients.length>=2;
  return `<div class="pat" data-p="${p.id}">
    <div class="n">${idx+1}</div>
    <div>
      <div class="grid">
        <div class="field"><label class="lbl" for="km-${p.id}">Distance en charge</label><div class="unit"><input id="km-${p.id}" class="ctl" type="number" inputmode="decimal" min="0" step="1" data-k="km" value="${esc(p.km)}"><span>km</span></div></div>
        ${shared?`<div class="field"><label class="lbl" for="ks-${p.id}">Dont seul dans le véhicule</label><div class="unit"><input id="ks-${p.id}" class="ctl" type="number" inputmode="decimal" min="0" step="1" data-k="kmSeul" value="${esc(p.kmSeul)}" placeholder="0"><span>km</span></div></div>`:''}
        <div class="field"><label class="lbl" for="pe-${p.id}">Péages du trajet</label><div class="unit"><input id="pe-${p.id}" class="ctl" type="number" inputmode="decimal" min="0" step="0.01" data-k="peage" value="${esc(p.peage)}" placeholder="0"><span>€</span></div></div>
        ${shared?`<div class="field"><label class="lbl" for="pd-${p.id}">Patients ayant passé le péage</label><input id="pd-${p.id}" class="ctl" type="number" inputmode="numeric" min="1" max="${t.patients.length}" step="1" data-k="peageDiv" value="${esc(p.peageDiv)}" placeholder="${t.patients.length}"></div>`:''}
        <div class="field"><label class="lbl" for="tx-${p.id}">Prise en charge</label><select id="tx-${p.id}" class="ctl" data-k="taux"><option value="0.55"${p.taux==='0.55'?' selected':''}>55 %</option><option value="1"${p.taux==='1'?' selected':''}>100 % (ALD, AT/MP, maternité, C2S…)</option></select></div>
      </div>
      <div class="opts">
        <label class="check"><input type="checkbox" data-k="tpmr"${p.tpmr?' checked':''}> Véhicule TPMR (+30 €)</label>
        <label class="check"><input type="checkbox" data-k="noFranchise"${p.noFranchise?' checked':''}> Exonéré de franchise (moins de 18 ans, maternité, C2S, AME)</label>
        ${shared?'':`<label class="check"><input type="checkbox" data-k="refus"${p.refus?' checked':''}> A refusé le transport partagé (facture hors tiers payant)</label>`}
      </div>
    </div></div>`;
}
function gvTag(t){const gv=tripGV(t), isGV=tripIsGV(t); return {cls:'tag '+(isGV?'on':''),txt:isGV?'Forfait Grande ville : '+(gv.reasons.join(' · ')||'réglé à la main'):'Forfait Grande ville non applicable'};}
function tripHTML(t,idx){
  const tag=gvTag(t), auto=periodeAuto(t.date,t.hDep,t.hArr,S.dept);
  return `<article class="trip" data-t="${t.id}">
    <div class="trip-head"><h3>${esc(t.label)}</h3>
      ${idx===0&&S.trips.length===1?`<button type="button" class="btn ghost sm" data-act="addReturn">Ajouter le retour</button>`:''}
      ${S.trips.length>1?`<button type="button" class="btn ghost sm" data-act="removeTrip">Retirer ce trajet</button>`:''}
    </div>
    <div class="trip-body">
      <div class="block">
        <div class="row two">
          <div class="field"><label class="lbl" for="from-${t.id}">Prise en charge</label><input id="from-${t.id}" class="ctl addr" type="text" data-k="from" value="${esc(t.from)}" placeholder="Adresse ou établissement" autocomplete="off"></div>
          <div class="field"><label class="lbl" for="to-${t.id}">Dépose</label><input id="to-${t.id}" class="ctl addr" type="text" data-k="to" value="${esc(t.to)}" placeholder="Adresse ou établissement" autocomplete="off"></div>
        </div>
        <div class="actions" style="margin-top:12px"><button type="button" class="btn sm" data-act="measure">Mesurer la distance</button><span class="hint" data-dist>${t.distance!=null?`${t.distance} km, reporté sur les patients.`:'Remplit la distance des patients et repère le forfait Grande ville.'}</span></div>
        <div class="tags" style="margin-top:10px"><span class="${tag.cls}" data-gvtag>${esc(tag.txt)}</span></div>
        <label class="check small"><input type="checkbox" data-k="gvManual"${t.gvAuto?'':' checked'}> Régler le forfait Grande ville à la main</label>
        ${t.gvAuto?'':`<div style="padding-left:28px"><span class="seg"><label><input type="radio" name="gv-${t.id}" value="1"${t.gv?' checked':''} data-k="gv"><span>Forfait appliqué</span></label><label><input type="radio" name="gv-${t.id}" value="0"${!t.gv?' checked':''} data-k="gv"><span>Sans forfait</span></label></span></div>`}
        <div class="map" data-map></div>
      </div>
      <div class="block">
        <div class="row">
          <div class="field"><label class="lbl" for="date-${t.id}">Date</label><input id="date-${t.id}" class="ctl" type="date" data-k="date" value="${esc(t.date)}"></div>
          <div class="field"><label class="lbl" for="hd-${t.id}">Heure de prise en charge</label><input id="hd-${t.id}" class="ctl" type="time" data-k="hDep" value="${esc(t.hDep)}"></div>
          <div class="field"><label class="lbl" for="ha-${t.id}">Heure de dépose</label><input id="ha-${t.id}" class="ctl" type="time" data-k="hArr" value="${esc(t.hArr)}"></div>
          <div class="field"><label class="lbl" for="per-${t.id}">Majoration de 50 %</label><select id="per-${t.id}" class="ctl" data-k="periode"><option value="auto"${t.periode==='auto'?' selected':''}>Automatique : ${esc(auto?.txt||'jour')}</option><option value="J"${t.periode==='J'?' selected':''}>Jour</option><option value="NU"${t.periode==='NU'?' selected':''}>Nuit</option><option value="DF"${t.periode==='DF'?' selected':''}>Samedi après 12 h, dimanche, férié</option></select></div>
        </div>
        <div class="checks" style="margin-top:10px">
          <label class="check"><input type="checkbox" data-k="hosp"${t.hosp?' checked':''}> Trajet lié à une hospitalisation (entrée ou sortie, ambulatoire, chimiothérapie, radiothérapie, dialyse)</label>
          <label class="check ${t.hosp?'':'off'}" style="padding-left:28px"><input type="checkbox" data-k="vide"${t.vide?' checked':''}${t.hosp?'':' disabled'}> Aller ou retour effectué à vide entre la station et l'établissement (tarif km +25 % sous 50 km, +50 % au-delà)</label>
        </div>
      </div>
      <div class="block">
        <div class="block-title"><h4>${t.patients.length>1?t.patients.length+' patients à bord, transport partagé':'1 patient'}</h4>
          <div class="actions"><button type="button" class="btn ghost sm" data-act="addPatient"${t.patients.length>=8?' disabled':''}>Ajouter un patient</button>${t.patients.length>1?`<button type="button" class="btn ghost sm" data-act="removePatient">Retirer le dernier</button>`:''}</div></div>
        ${t.patients.map((p,i)=>patientHTML(t,p,i)).join('')}
      </div>
    </div></article>`;
}
function renderTrips(){ $('#cpam2-trips').innerHTML=S.trips.map(tripHTML).join(''); initPlaces(); }
function renderTrip(t){ const el=$(`.trip[data-t="${t.id}"]`); if(!el) return renderTrips(); const focus=document.activeElement?.id; el.outerHTML=tripHTML(t,S.trips.indexOf(t)); initPlaces(); if(focus){const f=document.getElementById(focus); if(f){f.focus(); try{f.setSelectionRange(f.value.length,f.value.length);}catch(e){}}} }

function renderBill(){
  const R=computeAll();
  $('#cpam2-total').textContent=eur(R.total); $('#cpam2-barTotal').textContent=eur(R.total);
  const any=R.trips.some(t=>t.patients.length);
  if(!any){$('#cpam2-billBody').innerHTML='<p class="empty">Renseignez la distance en charge d\'au moins un patient : la facture se calcule au fur et à mesure.</p>';$('#cpam2-billFoot').hidden=true;$('#cpam2-rules').hidden=true;return;}
  $('#cpam2-billFoot').hidden=false; $('#cpam2-rules').hidden=false;
  const line=(l,v)=>`<div class="line sub"><span>${esc(l)}</span><span class="amt${v<0?' neg':''}">${v<0?'− '+eur(-v):eur(v)}</span></div>`;
  const html=[];
  if(R.errors.length) html.push(`<p class="hint err" style="padding:10px 0">${R.errors.map(esc).join('<br>')}</p>`);
  R.trips.forEach(t=>{
    if(!t.patients.length) return;
    html.push(`<div class="bill-group"><h3>${esc(t.label)} · ${esc(t.periodeTxt)}${t.nbMal>=2?' · '+t.nbMal+' patients':''}${t.isGV?' · Grande ville':''}</h3>`);
    t.patients.forEach(p=>{
      const L=[];
      L.push(line(`Forfait${p.forfaitBase===T.forfaitGV?' avec Grande ville':''} (4 km inclus)`,p.forfaitBase));
      L.push(line(`${fr(p.distFact,0)} km × ${fr(p.tarifKm)} €${p.coefVide?' (à vide +'+Math.round(p.coefVide*100)+' %)':''}`,p.kmBase));
      if(p.coefMaj) L.push(line('Majoration +50 %',r2(p.forfaitMaj+p.kmMaj)));
      if(p.suppForf) L.push(line(p.cd&&p.suppForf===p.cd?'Supplément courte distance':'Suppléments forfaitaires',p.suppForf));
      if(p.abatt) L.push(line(p.abTxt,-r2((p.forfaitBase+p.forfaitMaj+p.kmBase+p.kmMaj+p.suppForf)-(p.forfaitApres+p.kmApres+p.suppApres))));
      if(p.peage) L.push(line(`Péage${p.div>1?' ('+eur(p.peageBrut)+' ÷ '+p.div+')':''}`,p.peage));
      if(p.tpmr) L.push(line('Supplément TPMR',p.tpmr));
      html.push(`<div class="line pat-line"><span>Patient ${p.n}${p.refus?' · hors tiers payant (refus du partagé)':''}</span><span class="amt">${eur(p.tot)}</span></div>`+L.join('')+
        `<div class="line sub"><span>Part Assurance Maladie ${Math.round(p.taux*100)} %${p.franchise?', franchise de 4 € déduite au patient':''}</span><span class="amt">${eur(p.amo)}</span></div>`);
    });
    if(t.patients.length>1) html.push(`<div class="line sum"><span>Total ${esc(t.label.toLowerCase())}</span><span class="amt">${eur(t.total)}</span></div>`);
    html.push('</div>');
  });
  $('#cpam2-billBody').innerHTML=html.join('');
  $('#cpam2-amo').textContent=eur(R.amo); $('#cpam2-rac').textContent=eur(r2(R.total-R.amo));
  renderRules(R);
}
function renderRules(R){
  const L=localRules(S.dept), items=[];
  items.push('Barème unique depuis le 01/11/2025 : forfait de 13 € (28 € avec Grande ville) incluant 4 km, puis tarif kilométrique du département de l\'ADS. Arrondis au centime à chaque étape, comme le prévoit le cahier des charges de facturation Cnam.');
  if(R.trips.some(t=>t.patients.some(p=>p.coefVide))) items.push('Hospitalisation avec aller ou retour à vide : seul le trajet en charge est facturé, son tarif km est majoré de 25 % sous 50 km et de 50 % à partir de 50 km.');
  if(R.trips.some(t=>t.patients.some(p=>p.coefMaj))) items.push('Majoration de 50 % sur le forfait et les km : nuit si plus de la moitié du trajet a lieu entre 20 h et 8 h, sinon samedi dès 12 h, dimanche ou jour férié. Pas de cumul.');
  if(R.trips.some(t=>t.nbMal>=2)) items.push(`Transport partagé : une facture par patient sur sa propre distance ; abattement de 23 % (2), 35 % (3) ou 37 % (4 et plus), ramené à 5 % pour un patient resté seul sur au moins ${seuil()} km. Péage et TPMR ne sont jamais abattus.`);
  if(R.trips.some(t=>t.patients.some(p=>p.cd))) items.push('Supplément courte distance : 2 € par trajet strictement inférieur à 10 km (trajets à partir du 01/08/2026), exclu avec le forfait Grande ville, non majoré, abattu en transport partagé.');
  L.notes.forEach(n=>items.push(`CPAM ${S.dept} : ${n}`));
  $('#cpam2-rulesList').innerHTML=items.map(i=>`<li>${esc(i)}</li>`).join('');
}
function billText(){
  const R=computeAll(), d=deptOf(S.dept), out=[`Transport taxi conventionné, département ${S.dept} (${d.name}), ${fr(d.rate)} €/km`];
  R.trips.forEach(t=>{ if(!t.patients.length) return; out.push('',`${t.label} (${t.periodeTxt}${t.nbMal>=2?', '+t.nbMal+' patients':''}${t.isGV?', Grande ville':''})`);
    t.patients.forEach(p=>{out.push(`  Patient ${p.n} : ${eur(p.tot)} (part AMO ${Math.round(p.taux*100)} % : ${eur(p.amo)})`);
      out.push(`    forfait ${eur(p.forfaitBase)} ; ${fr(p.distFact,0)} km x ${fr(p.tarifKm)} € = ${eur(p.kmBase)}${p.coefMaj?' ; majoration +50 % '+eur(r2(p.forfaitMaj+p.kmMaj)):''}${p.suppForf?' ; suppléments '+eur(p.suppForf):''}${p.abatt?' ; '+p.abTxt:''}${p.peage?' ; péage '+eur(p.peage):''}${p.tpmr?' ; TPMR 30,00 €':''}`);});});
  out.push('',`Total : ${eur(R.total)} ; part Assurance Maladie : ${eur(R.amo)} ; reste : ${eur(r2(R.total-R.amo))}`);
  return out.join('\n');
}

/* ===================== GOOGLE MAPS ===================== */
let gReady=false, gDir=null;
window.cpamCalcInit_v2=function(){gReady=true;initPlaces();};
function initPlaces(){
  if(!gReady||!window.google?.maps?.places) return;
  $$('input.addr').forEach(el=>{ if(el.dataset.ac) return; el.dataset.ac='1';
    try{const ac=new google.maps.places.Autocomplete(el,{componentRestrictions:{country:['fr','mc']}});
      ac.addListener('place_changed',()=>{const pl=ac.getPlace()||{}; const t=tripOf(el); if(!t) return;
        const k=el.dataset.k; t[k]=el.value; t[k+'Full']=(pl.name&&pl.formatted_address)?pl.name+', '+pl.formatted_address:''; updateGvTag(t); renderBill();});
    }catch(e){}});
}
function updateGvTag(t){const el=$(`.trip[data-t="${t.id}"] [data-gvtag]`); if(!el) return; const tag=gvTag(t); el.className=tag.cls; el.textContent=tag.txt;}
async function measure(t,btn){
  const info=$(`.trip[data-t="${t.id}"] [data-dist]`);
  if(!t.from||!t.to){info.textContent='Renseignez la prise en charge et la dépose.';return;}
  if(!window.google?.maps){info.textContent='Google Maps indisponible : saisissez la distance à la main.';return;}
  try{
    btn.disabled=true; info.textContent='Calcul en cours';
    gDir=gDir||new google.maps.DirectionsService();
    const res=await gDir.route({origin:t.from,destination:t.to,travelMode:google.maps.TravelMode.DRIVING,provideRouteAlternatives:true,drivingOptions:{departureTime:new Date()},avoidTolls:S.avoidTolls,avoidHighways:S.avoidHighways});
    if(!res?.routes?.length) throw new Error('aucun itinéraire');
    let best=0,m=Infinity; res.routes.forEach((r,i)=>{const d=(r.legs||[]).reduce((s,l)=>s+(l.distance?.value||0),0); if(d<m){m=d;best=i;}});
    const km=S.round==='up'?Math.ceil(m/1000):Math.floor(m/1000);
    const legs=res.routes[best].legs||[];
    t.fromFull=[t.fromFull,legs[0]?.start_address].filter(Boolean).join(' | '); t.toFull=[t.toFull,legs[legs.length-1]?.end_address].filter(Boolean).join(' | ');
    t.distance=km; t.patients.forEach(p=>{ if(!num(p.km)) p.km=String(km); });
    renderTrip(t); renderBill();
    const mapEl=$(`.trip[data-t="${t.id}"] [data-map]`); mapEl.style.display='block';
    const map=new google.maps.Map(mapEl,{mapTypeControl:false,streetViewControl:false});
    const rend=new google.maps.DirectionsRenderer({map}); rend.setDirections(res); if(rend.setRouteIndex) rend.setRouteIndex(best);
  }catch(e){info.textContent='Distance non calculée ('+(e.message||'erreur')+') : saisissez-la à la main.';}
  finally{btn.disabled=false;}
}

/* ===================== ÉVÉNEMENTS ===================== */
const tripOf=el=>{const a=el.closest('.trip'); return a&&S.trips.find(t=>String(t.id)===a.dataset.t);};
const patOf=el=>{const t=tripOf(el), a=el.closest('.pat'); return t&&a?{t,p:t.patients.find(p=>String(p.id)===a.dataset.p)}:null;};
root.addEventListener('input',e=>{
  const el=e.target, k=el.dataset.k; if(!k) return;
  const pp=patOf(el);
  if(pp){ pp.p[k]=el.type==='checkbox'?el.checked:el.value; renderBill(); return; }
  const t=tripOf(el); if(!t) return;
  if(k==='from'||k==='to'){ t[k]=el.value; t[k+'Full']=''; t.distance=null; updateGvTag(t); renderBill(); return; }
  if(['date','hDep','hArr'].includes(k)){ t[k]=el.value; const auto=periodeAuto(t.date,t.hDep,t.hArr,S.dept); const sel=$(`.trip[data-t="${t.id}"] select[data-k="periode"]`); if(sel) sel.options[0].textContent='Automatique : '+(auto?.txt||'jour'); renderBill(); return; }
});
root.addEventListener('change',e=>{
  const el=e.target, k=el.dataset.k, id=el.id;
  if(id==='cpam2-dept'){S.dept=el.value;S.seuilSeul='';renderLocal();savePrefs();renderTrips();renderBill();return;}
  if(id==='cpam2-cd'){S.courteDistance=el.checked;S.cdAuto=false;savePrefs();renderBill();return;}
  if(id==='cpam2-seuil'){S.seuilSeul=el.value;renderBill();return;}
  if(id==='cpam2-suppLocal'){S.suppLocal=el.value;savePrefs();renderBill();return;}
  if(el.name==='cpam2-round'){S.round=el.value;savePrefs();return;}
  if(id==='cpam2-avoidTolls'){S.avoidTolls=el.checked;return;} if(id==='cpam2-avoidHighways'){S.avoidHighways=el.checked;return;}
  if(!k) return;
  const pp=patOf(el);
  if(pp){ if(el.tagName==='SELECT'||el.type==='checkbox'){pp.p[k]=el.type==='checkbox'?el.checked:el.value;renderBill();} return; }
  const t=tripOf(el); if(!t) return;
  if(k==='hosp'){t.hosp=el.checked; if(!t.hosp) t.vide=false; renderTrip(t); renderBill(); return;}
  if(k==='vide'){t.vide=el.checked; renderBill(); return;}
  if(k==='periode'){t.periode=el.value; renderBill(); return;}
  if(k==='gvManual'){t.gvAuto=!el.checked; if(!t.gvAuto) t.gv=tripGV(t).auto; renderTrip(t); renderBill(); return;}
  if(k==='gv'){t.gv=el.value==='1'; updateGvTag(t); renderBill(); return;}
});
root.addEventListener('click',e=>{
  const b=e.target.closest('[data-act]'); if(!b) return; e.preventDefault();
  const act=b.dataset.act, t=tripOf(b);
  if(act==='addReturn'&&t){const r=newTrip('Retour'); r.from=t.to; r.to=t.from; r.fromFull=t.toFull; r.toFull=t.fromFull; r.date=t.date; r.hDep=''; r.hosp=t.hosp; r.vide=t.vide; r.patients=t.patients.map(p=>Object.assign(newPatient(),{km:p.km,peage:p.peage,peageDiv:p.peageDiv,tpmr:p.tpmr,taux:p.taux,noFranchise:p.noFranchise})); S.trips.push(r); renderTrips(); renderBill(); $(`.trip[data-t="${r.id}"] input[data-k="hDep"]`)?.focus(); return;}
  if(act==='removeTrip'&&t){S.trips=S.trips.filter(x=>x!==t); renderTrips(); renderBill(); return;}
  if(act==='addPatient'&&t){if(t.patients.length<8){t.patients.push(newPatient()); renderTrip(t); renderBill(); $(`.trip[data-t="${t.id}"] .pat:last-of-type input[data-k="km"]`)?.focus();} return;}
  if(act==='removePatient'&&t){if(t.patients.length>1){t.patients.pop(); renderTrip(t); renderBill();} return;}
  if(act==='measure'&&t){measure(t,b); return;}
  if(act==='reset'){S.trips=[newTrip('Aller')]; S.seuilSeul=''; renderLocal(); renderTrips(); renderBill(); window.scrollTo({top:0,behavior:'smooth'}); return;}
  if(act==='copy'){const txt=billText(); const done=()=>{b.textContent='Détail copié'; setTimeout(()=>b.textContent='Copier le détail',1800);};
    (navigator.clipboard?.writeText?navigator.clipboard.writeText(txt).then(done):Promise.reject()).catch(()=>{const ta=document.createElement('textarea');ta.value=txt;root.appendChild(ta);ta.select();try{document.execCommand('copy');done();}catch(e){}ta.remove();}); return;}
  if(act==='toBill'){$('#cpam2-bill').scrollIntoView({behavior:'smooth',block:'start'}); return;}
});
root.addEventListener('keydown',e=>{ if(e.key==='Enter'&&e.target.tagName==='INPUT'&&e.target.type!=='checkbox'){e.preventDefault(); const t=tripOf(e.target); if(t&&e.target.classList.contains('addr')) measure(t,$(`.trip[data-t="${t.id}"] [data-act="measure"]`)); } });

renderStatic(); renderTrips(); renderBill();
})();
JS;

    wp_register_script('cpam2-js', false, array(), null, true);
    wp_enqueue_script('cpam2-js');
    wp_add_inline_script('cpam2-js', $js);

    /* Google Maps (Places + Directions) */
    $key = 'AIzaSyCqyT4qHRHK9kkSSC66YqHJ_V_Z99OsJGc'; // clé Google Maps : NE PAS SUPPRIMER
    $url = 'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode($key) . '&libraries=places&language=fr&region=FR&callback=cpamCalcInit_v2';
    wp_enqueue_script('cpam2-gmaps', $url, array('cpam2-js'), null, true);
  }

  /* ================= HTML ================= */
  ob_start(); ?>
  <div id="cpam2-root">
    <div class="wrap">
      <header class="head">
        <div>
          <h1>Calculateur taxi conventionné</h1>
          <p class="sub">Convention-cadre 2025-2029, tarifs en vigueur depuis le 1<sup>er</sup> novembre 2025. La facture se calcule pendant la saisie.</p>
        </div>
        <div class="dept">
          <label class="lbl" for="cpam2-dept">Département de l'ADS</label>
          <select id="cpam2-dept" class="ctl"></select>
        </div>
      </header>

      <div class="cols">
        <div>
          <section class="sec">
            <div class="lead"><h2>Trajets</h2><p>Un trajet par ligne de facture, aller et retour séparés. Le forfait Grande ville, la majoration de nuit ou de dimanche et le supplément courte distance se déduisent de ce que vous saisissez.</p></div>
            <div id="cpam2-trips"></div>
          </section>

          <section class="sec">
            <div class="lead"><h2>Mesure de la distance</h2><p>Trajet le plus court entre les deux adresses, en km entiers.</p></div>
            <div class="row">
              <div class="field"><span class="lbl">Arrondi</span>
                <span class="seg"><label><input type="radio" name="cpam2-round" value="down" checked><span>Au km inférieur</span></label><label><input type="radio" name="cpam2-round" value="up"><span>Au km supérieur</span></label></span></div>
              <div class="field"><span class="lbl">Itinéraire</span>
                <div class="checks"><label class="check small"><input type="checkbox" id="cpam2-avoidTolls"> Éviter les péages</label><label class="check small"><input type="checkbox" id="cpam2-avoidHighways"> Éviter les autoroutes</label></div></div>
            </div>
          </section>

          <section class="sec">
            <div class="lead"><h2>Convention locale</h2><p>Valeurs propres au département de l'ADS, pré-remplies d'après la convention publiée par la CPAM.</p></div>
            <div>
              <div id="cpam2-local" class="hint" style="margin-bottom:14px;font-size:14px;display:flex;flex-direction:column;gap:4px"></div>
              <div class="row">
                <div class="field"><label class="lbl" for="cpam2-seuil">Seuil « patient seul »</label><div class="unit"><input id="cpam2-seuil" class="ctl" type="number" min="30" step="1" value="30"><span>km</span></div></div>
                <div class="field"><label class="lbl" for="cpam2-suppLocal">Autre supplément local validé Cnam</label><div class="unit"><input id="cpam2-suppLocal" class="ctl" type="number" min="0" step="0.01" placeholder="0"><span>€</span></div></div>
              </div>
              <label class="check" style="margin-top:8px"><input type="checkbox" id="cpam2-cd" checked> Supplément courte distance de 2 € pour les trajets de moins de 10 km (à partir du 01/08/2026)</label>
            </div>
          </section>

          <p class="foot">Sources : arrêté du 29 juillet 2025 (convention-cadre taxis), cahier des charges Cnam de facturation B2 taxis, liste Cnam des établissements Grande ville au 1<sup>er</sup> avril 2026, conventions locales publiées sur ameli.fr. Montants indicatifs : la facture validée par la CPAM fait foi.</p>
        </div>

        <aside class="bill" id="cpam2-bill" aria-live="polite">
          <div class="bill-head"><h2>Facture</h2><span class="total" id="cpam2-total">0,00 €</span></div>
          <div class="bill-body" id="cpam2-billBody"></div>
          <div class="bill-foot" id="cpam2-billFoot" hidden>
            <div class="split"><div><span class="lbl">Part Assurance Maladie</span><b id="cpam2-amo">0,00 €</b></div><div><span class="lbl">Reste patient ou mutuelle</span><b id="cpam2-rac">0,00 €</b></div></div>
            <div class="actions"><button type="button" class="btn ghost sm" data-act="copy">Copier le détail</button><button type="button" class="btn ghost sm" data-act="reset">Nouvelle facture</button>
              <a href="https://cpam.taxiapp.club/regle-de-calcul/" target="_blank" rel="noopener" class="btn ghost sm">Règles de calcul</a></div>
          </div>
          <details class="rules" id="cpam2-rules" hidden><summary>Règles appliquées</summary><ul id="cpam2-rulesList"></ul></details>
        </aside>
      </div>

      <div class="bar"><span><span class="lbl">Total</span><br><b id="cpam2-barTotal">0,00 €</b></span><button type="button" class="btn" data-act="toBill">Voir la facture</button></div>
    </div>
  </div>
  <?php
  return ob_get_clean();
});
