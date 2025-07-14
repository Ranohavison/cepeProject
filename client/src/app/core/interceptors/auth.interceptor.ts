import { HttpInterceptorFn } from "@angular/common/http";

export const authInterceptorFn: HttpInterceptorFn= (req, next) => {
  const token = localStorage.getItem('token');
  if (token) {
    const clonedReq = req.clone({ setHeaders: { Authorization: `Token ${token}`}});
    
    return next(clonedReq);
  }
  return next(req)
}
