<%--
 * Copyright (c) 2005 Daffodil Software Ltd all rights reserved.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of version 2 of the GNU General Public License as
 * published by the Free Software Foundation.
 * There are special exceptions to the terms and conditions of the GPL
 * as it is applied to this software. See the GNU General Public License for more details.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
--%>

<html>
  <head>
    <title>Login</title>
    <LINK media="all" type="text/css" href="themes/leads/style.css" rel="stylesheet">
  </head>
<body background="" onload="document.userForm.userLoginBox.focus()">
<%@ page import="javax.servlet.http.*,java.util.*, java.sql.*,java.io.*,sourceleads.*,sourceleads.mail.*" errorPage="errorpage.jsp"%>
<%
	String requestUser = request.getParameter("userLoginBox");
      	String requestPassword = request.getParameter("passwordBox");
	String isAdmin = request.getParameter("isAdmin");
               String user=null;
               String pass=null;
               boolean rights=false;
      String message=null;
	if(requestUser!= null)
      {
		ConnectionProvider connectionProvider=(ConnectionProvider)session.getAttribute("ConnectionProvider");
		if(connectionProvider==null)
                {
                  connectionProvider=new ConnectionProvider(application.getInitParameter("DBConnectionURL"),application.getInitParameter("User"),application.getInitParameter("Password"));
                }
               	Connection connection =	connectionProvider.getConnection();
               session.setAttribute("ConnectionProvider",connectionProvider);
             Statement stmt = connection.createStatement();
             String selectQuery="select * from users where loginid='"+requestUser+"'"+" and password='"+requestPassword+"'";
             ResultSet rs =  stmt.executeQuery(selectQuery);
//		System.out.println("selectQuery="+selectQuery);
             if(rs.next()){

               		session.setAttribute("userId",rs.getString("id"));
             		session.setAttribute("userLoginValue",requestUser);
             		session.setAttribute("passwordValue",requestPassword);
                       	rights=rs.getBoolean("adminrights");

                        if(isAdmin==null)
                        {
	                session.setAttribute("administrator","false");
                        %>
                        <jsp:forward page="InteractionFrame.jsp">
                          <jsp:param name="page" value="allleads.jsp"/>
                          <jsp:param name="heading" value="Leads"/>
                        </jsp:forward>
                        <%
                         }
                         else if(rights)
                         {
	                session.setAttribute("administrator","true");
                        %>
                        <jsp:forward page="InteractionFrame.jsp">
                          <jsp:param name="page" value="allleads.jsp"/>
                          <jsp:param name="heading" value="Leads"/>
                        </jsp:forward>
                        <%
                         }
                         else
                         {
                             message="User does not have administrator rights";
                         }
            }
            else
            {
          message="Wrong Username or Password";
            }
        }
%>
<jsp:include flush="false" page="login.jsp">
<jsp:param name="message" value="<%=message%>"/>
</jsp:include>
</body>
</html>
