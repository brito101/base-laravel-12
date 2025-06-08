$("#google2fa").on("switchChange.bootstrapSwitch",function(t,n){$.ajax({headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},url:$(t.target).data("action"),type:"POST",data:{data:n,user:$(t.target).data("user")},success:function({message:e,qrcode:r,seed:a}){alert(e),a?($("#seed-container div").remove(),$("#seed-container").append(`<div>
                        <p class="w-100 d-inline-block px-0">Semente: <span style="letter-spacing: .2rem; margin-left: 20px; font-weight: 700;">${a}</span></p>
                        <img src="data:image/png;base64,${r}" alt="QRCode" />
                    </div>`)):$("#seed-container div").remove()},error:function(e){alert(e)}})});
//# sourceMappingURL=google2fa-DUmHlKHb.js.map
