<?php

namespace SMS\FluidComponents\Interfaces;

/**
 * Marker interface to determine if a component parameter should
 * be escaped in a way that HTML in fluid variables isn't interpreted
 *
 * In the following example, the script tag will be html-escaped if myParam
 * is an EscapedParameter:
 *
 * ::
 *
 *       <f:variable name="myVariable">
 *           <script>alert('test');</script>
 *       </f:variable>
 *       <my:component myParam="<b>my content</b>{myVariable}" />
 *
 */
interface EscapedParameter
{
}
