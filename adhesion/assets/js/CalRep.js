// JavaScript Document
function CalRep(jourRep,moisRep,anneeRep) {
var jour= Array(Array(20,19,21,20,20,19,19,18,22,22,21,21),
/* 1792 */
Array(20,19,21,20,20,19,19,18,22,22,21,21),
/* 1793 */
Array(20,19,21,20,20,19,19,18,22,22,21,21),
/* 1794 */
Array(20,19,21,20,20,19,19,18,23,23,22,22),
/* 1795 */
Array(21,20,21,20,20,19,19,18,22,22,21,21),
/* 1796 */
Array(20,19,21,20,20,19,19,18,22,22,21,21),
/* 1797 */
Array(20,19,21,20,20,19,19,18,22,22,21,21),
/* 1798 */
Array(20,19,21,20,20,20,19,18,23,23,22,22),
/* 1799 */
Array(21,21,22,21,21,20,20,19,23,23,22,22),
/* 1800 */
Array(21,20,22,21,21,20,20,19,23,23,22,22),
/* 1801 */
Array(21,20,22,21,21,20,20,19,23,23,22,22),
/* 1802 */
Array(21,20,22,21,21,20,20,19,24,24,23,23),
/* 1803 */
Array(22,21,22,21,21,20,20,19,23,23,22,22),
/* 1804 */
Array(21,20,22,21,21,20,20,19,23,23,22,22)
/* 1805 */
	);
	this.jourRep=jourRep;
	this.moisRep=moisRep;
	this.anneeRep=anneeRep;
	this.jourComp=0;
	this.jourGreg=0;
  this.moisGreg=0;
	this.anneeGreg=0;
	this.dateGreg=0;
function estBissextile(annee) {
	return annee%4==0;
}
function Nbre2Chiffres(nb) {
	if (nb<10)      return "0"+nb;
	else      return String(nb);
}
function nbJourMois(mois,annee) {
if (mois==2) {
	if (estBissextile(annee))return 29;
	else return 28;
}
	if (mois==1 || mois==3 || mois==5 || mois==7 || mois==8 || mois==10 || mois==12)      return 31;
	else return 30;
}
this.setJourRep = function(jourRep) {
	this.jourRep=jourRep;
}
	;
this.setMoisRep = function(moisRep) {
	this.moisRep=moisRep;
}
	;
this.setAnneeRep = function(anneeRep) {
	this.anneeRep=anneeRep;
}
	;
this.setJourComp = function(jourComp) {
	this.jourComp=jourComp;
}
	;
this.convertir = function() {
if (this.moisRep==13) {
	this.anneeGreg=1792+this.anneeRep;
	this.moisGreg=9;
if (this.anneeGreg<=1800) {
	this.jourGreg=16+this.jourRep;
}
else {
	this.jourGreg=17+this.jourRep;
}
}
else {
	this.moisGreg= this.moisRep+8;
	this.anneeGreg= 1791+this.anneeRep;
if (this.moisGreg>12) {
	this.moisGreg-=12;
	this.anneeGreg++;
}
	this.jourGreg= jour[this.anneeGreg-1792][this.moisGreg-1]+this.jourRep-1;
if (this.jourGreg>nbJourMois(this.moisGreg,this.anneeGreg)) {
	this.jourGreg-=nbJourMois(this.moisGreg,this.anneeGreg);
	this.moisGreg++;
if (this.moisGreg>12) {
	this.moisGreg-=12;
	this.anneeGreg++;
}
}
}
	this.dateGreg=Nbre2Chiffres(this.jourGreg)+'/'+Nbre2Chiffres(this.moisGreg)+'/'+this.anneeGreg;
	console.log(this.dateGreg);
}
	;
this.getJourGreg = function() {
	return Nbre2Chiffres(this.jourGreg);
}
	;
this.getMoisGreg = function() {
	return this.moisGreg;
}
	;
this.getAnneeGreg = function() {
	return this.anneeGreg;
}
	;
this.getDateGreg = function() {
	return this.dateGreg;
}
	;
this.getDateRepNim = function() {
var mois_revolutionnaires_nimegue       = {
	1: 'Vend', 2: 'Brum', 3: 'Frim', 4: 'Nivo', 5: 'Pluv', 6: 'Vent', 7: 'Germ', 8: 'Flor', 9: 'Prai', 10: 'Mess', 11: 'Ther', 12: 'Fruc', 13: 'Comp'
}
	;
	var DateNim = Nbre2Chiffres(this.jourRep)+'/'+mois_revolutionnaires_nimegue[this.moisRep]+'/'+Nbre2Chiffres(this.anneeRep);
	return DateNim;
}
	;
}