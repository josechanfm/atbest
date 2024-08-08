import { usePage } from '@inertiajs/vue3';

const hasPermission = (permission) => {
    if(!usePage().props.user.permissions){
        return false;
    }
    const userPermissions=usePage().props.user.permissions.map(p=>p.name);
    if(Array.isArray(permission)){
        return permission.some(p=>{
            return userPermissions.includes(p)
        })
    }else{
        return userPermissions.includes(permission)
    }
   
}

function checkPermission(el, binding) {
    const { value } = binding
    if (!hasPermission(value)) {
        el.style.display = 'none';
        //el.parentNode && el.parentNode.removeChild(el)
    }
}

const hasRole = (role) => {
    const userRoles=usePage().props.user.roles.map(u=>u.name);
    if(Array.isArray(role)){
         return role.some(r=>{
            return userRoles.includes(r)
         })
    }else{
        return userRoles.includes(role)
    }

}


function checkRole(el, binding) {
    const { value } = binding
    if (!hasRole(value)) {
        el.style.display = 'none';
        //el.parentNode && el.parentNode.removeChild(el)
    }
}

const permission = {
    created (el, binding) {
        checkPermission(el, binding)
    },
    beforeUpdate (el, binding) {
        checkPermission(el, binding)
    }
}
const role = {
    created (el, binding) {
        checkRole(el, binding)
    },
    beforeUpdate (el, binding) {
        checkRole(el, binding)
    }
}


export default {
    install (App, options) {
        App.directive('permission', permission)
        App.directive('role', role)
        App.config.globalProperties.$can = hasPermission
        App.config.globalProperties.$is = hasRole
    }
}