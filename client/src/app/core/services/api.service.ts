import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private apiUrl = 'http://127.0.0.1:8080/api/';

  constructor(private http: HttpClient) { }

  generatePdf(eleveId: string): void {
    this.http.get(`${this.apiUrl}bulletin/${eleveId}`, { responseType: 'blob'}).subscribe(
      blob => {
        const link = document.createElement('a');
        const url = window.URL.createObjectURL(blob);
        link.href = url;
        link.download = `bulletin_${eleveId}.pdf`;
        link.click();
        window.URL.revokeObjectURL(url);
      }
    );
  }

  getData(endPoint: string, params : object = {}): Observable<any> {
    return this.http.post(`${this.apiUrl}${endPoint}`, params);
  }

  postData(endpoint: string, data: any): Observable<any> {
    const url = `${this.apiUrl}${endpoint}`;
    return this.http.post(url, data, { headers: new HttpHeaders({ 'Content-Type': 'application/json' }) });
  }

  deleteItem(endpoint: string, primaryKey: string) : Observable<any> {
    const url = `${this.apiUrl}${endpoint}/${primaryKey}`;
    return this.http.delete(url);
  }

  updateItem(endpoint: string, primaryKey: string, data: any) : Observable<any> {
    const url = `${this.apiUrl}${endpoint}/${primaryKey}`;
    return this.http.put(url, data);
  }
}