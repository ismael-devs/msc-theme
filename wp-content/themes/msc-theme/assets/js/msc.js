/**
 * MSC · JS mínimo, sin dependencias. Todo funciona también sin JS (mejora progresiva).
 */
( function () {
	'use strict';

	var T = window.mscTextos || {};
	var escritorio = window.matchMedia( '(min-width: 821px)' );

	function $$( sel, ctx ) {
		return Array.prototype.slice.call( ( ctx || document ).querySelectorAll( sel ) );
	}

	/* ---------- Menú móvil ---------- */
	var botonMenu = document.querySelector( '[data-msc-menu]' );
	var menu = document.getElementById( 'msc-menu-movil' );
	function abrirMenu( abrir ) {
		if ( ! botonMenu || ! menu ) {
			return;
		}
		menu.hidden = ! abrir;
		botonMenu.setAttribute( 'aria-expanded', abrir ? 'true' : 'false' );
		var texto = botonMenu.querySelector( '[data-msc-menu-texto]' );
		if ( texto ) {
			texto.textContent = abrir ? ( T.cerrar || 'Cerrar menú' ) : ( T.abrir || 'Abrir menú' );
		}
	}
	if ( botonMenu && menu ) {
		botonMenu.addEventListener( 'click', function () {
			abrirMenu( menu.hidden );
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! menu.hidden ) {
				abrirMenu( false );
				botonMenu.focus();
			}
		} );
		escritorio.addEventListener( 'change', function ( e ) {
			if ( e.matches ) {
				abrirMenu( false );
			}
		} );
	}

	/* ---------- Buscador del header ---------- */
	var botonBuscar = document.querySelector( '[data-msc-buscar]' );
	var buscador = document.getElementById( 'msc-buscador' );
	if ( botonBuscar && buscador ) {
		botonBuscar.setAttribute( 'role', 'button' );
		botonBuscar.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var abrir = buscador.hidden;
			buscador.hidden = ! abrir;
			botonBuscar.setAttribute( 'aria-expanded', abrir ? 'true' : 'false' );
			if ( abrir ) {
				buscador.querySelector( 'input' ).focus();
			}
		} );
		buscador.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) {
				buscador.hidden = true;
				botonBuscar.setAttribute( 'aria-expanded', 'false' );
				botonBuscar.focus();
			}
		} );
	}

	/* ---------- Marquee: un toque lo detiene, otro lo reanuda ---------- */
	$$( '[data-msc-marquee]' ).forEach( function ( m ) {
		m.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( 'a' ) ) {
				return;
			}
			m.classList.toggle( 'is-detenido' );
		} );
	} );

	/* ---------- Filtros: abiertos en escritorio; en móvil, cerrados salvo que haya filtros activos ---------- */
	$$( '[data-msc-filtros]' ).forEach( function ( d ) {
		var activo = $$( 'input, select', d ).some( function ( c ) {
			return '' !== c.value;
		} );
		d.open = escritorio.matches || activo;
	} );

	/* ---------- Visor de imágenes de la pieza ---------- */
	$$( '[data-msc-visor]' ).forEach( function ( v ) {
		var img = v.querySelector( '.msc-visor__img' );
		$$( '.msc-visor__miniaturas a', v ).forEach( function ( a ) {
			a.addEventListener( 'click', function ( e ) {
				if ( ! img ) {
					return;
				}
				e.preventDefault();
				img.src = a.getAttribute( 'href' );
				if ( a.dataset.srcset ) {
					img.srcset = a.dataset.srcset;
				}
				img.alt = a.dataset.alt || '';
				$$( '.msc-visor__miniaturas a', v ).forEach( function ( o ) {
					o.removeAttribute( 'aria-current' );
				} );
				a.setAttribute( 'aria-current', 'true' );
			} );
		} );
	} );

	/* ---------- Compartir ---------- */
	$$( '[data-msc-compartir]' ).forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			var url = b.dataset.url || location.href;
			var estado = b.parentNode.querySelector( '.msc-compartir-estado' );
			if ( navigator.share ) {
				navigator.share( { title: b.dataset.titulo || document.title, url: url } ).catch( function () {} );
			} else if ( navigator.clipboard ) {
				navigator.clipboard.writeText( url ).then( function () {
					if ( estado ) {
						estado.textContent = T.copiado || 'Enlace copiado';
					}
				} );
			}
		} );
	} );

	/* ---------- Mapa bajo demanda ---------- */
	$$( '[data-msc-mapa]' ).forEach( function ( m ) {
		var b = m.querySelector( 'button' );
		if ( ! b ) {
			return;
		}
		b.addEventListener( 'click', function () {
			var f = document.createElement( 'iframe' );
			f.src = m.dataset.mscMapa;
			f.title = b.textContent.trim();
			f.loading = 'lazy';
			f.referrerPolicy = 'no-referrer-when-downgrade';
			f.setAttribute( 'allowfullscreen', '' );
			m.replaceChildren( f );
		} );
	} );

	/* ---------- Formularios: campos condicionales y validación accesible ---------- */
	$$( '[data-msc-form]' ).forEach( function ( form ) {
		var condicionales = $$( '[data-msc-condicion]', form );
		function actualizar() {
			condicionales.forEach( function ( c ) {
				var elegido = form.querySelector( 'input[name="' + c.dataset.mscCondicion + '"]:checked' );
				var visible = !! elegido && elegido.value === c.dataset.mscValor;
				c.hidden = ! visible;
				$$( 'input, textarea, select', c ).forEach( function ( i ) {
					i.disabled = ! visible;
				} );
				var nota = c.querySelector( '.msc-form__condicion' );
				if ( nota ) {
					nota.hidden = true;
				}
			} );
		}
		if ( condicionales.length ) {
			form.addEventListener( 'change', actualizar );
			actualizar();
		}

		form.addEventListener( 'submit', function ( e ) {
			var errores = [];
			$$( '.msc-form__error[data-js]', form ).forEach( function ( p ) {
				p.remove();
			} );
			$$( '.msc-form__campo', form ).forEach( function ( campo ) {
				campo.classList.remove( 'has-error' );
				if ( campo.hidden ) {
					return;
				}
				var control = campo.querySelector( 'input:not([type=hidden]), textarea, select' );
				if ( ! control || control.disabled ) {
					return;
				}
				var mensaje = '';
				if ( 'radio' === control.type ) {
					if ( control.required && ! campo.querySelector( 'input:checked' ) ) {
						mensaje = T.requerido;
					}
				} else if ( 'checkbox' === control.type ) {
					if ( control.required && ! control.checked ) {
						mensaje = T.requerido;
					}
				} else if ( control.required && '' === control.value.trim() ) {
					mensaje = T.requerido;
				} else if ( 'email' === control.type && control.value && ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( control.value ) ) {
					mensaje = T.correo;
				}
				if ( mensaje ) {
					var etiqueta = campo.querySelector( 'label, legend' );
					errores.push( control );
					campo.classList.add( 'has-error' );
					var p = document.createElement( 'p' );
					p.className = 'msc-form__error';
					p.dataset.js = '1';
					p.id = ( control.id || control.name ) + '-error-js';
					p.textContent = mensaje + ( etiqueta ? ' (' + etiqueta.textContent.replace( '*', '' ).trim() + ')' : '' );
					campo.appendChild( p );
					control.setAttribute( 'aria-invalid', 'true' );
					control.setAttribute( 'aria-describedby', p.id );
				} else {
					control.removeAttribute( 'aria-invalid' );
				}
			} );
			if ( errores.length ) {
				e.preventDefault();
				var estado = form.querySelector( '.msc-form__estado' );
				if ( estado ) {
					estado.innerHTML = '<p class="msc-aviso msc-aviso--error">' + ( T.revisa || '' ) + '</p>';
				}
				errores[ 0 ].focus();
			}
		} );

		// Tras un envío (éxito o error), lleva el foco al mensaje.
		var aviso = form.querySelector( '.msc-form__estado .msc-aviso' );
		if ( aviso && location.hash === '#' + form.id ) {
			form.querySelector( '.msc-form__estado' ).focus();
		}
	} );
}() );
